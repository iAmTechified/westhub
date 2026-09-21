/**
 * WestHub → Google Sheets bridge.
 *
 * Lets the WestHub website write job applications and promo claims into this
 * spreadsheet with no Google Cloud project and no service account key. The
 * script runs as you, the spreadsheet owner, and only accepts requests that
 * carry the shared secret.
 *
 * SETUP (full walkthrough: docs/DEPLOYMENT.md, section 4.2)
 *   1. In this spreadsheet: Extensions → Apps Script. Replace everything in the
 *      editor with this file and save.
 *   2. Project Settings (gear icon) → Script properties → Add script property:
 *        Property: WESTHUB_SECRET
 *        Value:    a long random string (40+ characters)
 *   3. Deploy → New deployment → type "Web app":
 *        Execute as:     Me
 *        Who has access: Anyone
 *      Authorise when asked, then copy the Web app URL (ends in /exec).
 *   4. In WestHub admin → Settings → Integrations, choose "Apps Script web
 *      app", paste the URL and the same secret, save, then Test connection.
 *
 * "Anyone" is required because the website calls this without a Google
 * sign-in. Requests without the matching secret are refused, and the URL is
 * only ever used by the WestHub server, never shown to visitors.
 *
 * After editing this script, publish the change with Deploy → Manage
 * deployments → edit (pencil) → Version: New version. Saving alone does not
 * update the live web app.
 */

const SECRET_PROPERTY = 'WESTHUB_SECRET';

function doPost(e) {
  let request;

  try {
    request = JSON.parse((e && e.postData && e.postData.contents) || '{}');
  } catch (err) {
    return respond({ ok: false, error: 'The request body was not valid JSON.' });
  }

  const expected = PropertiesService.getScriptProperties().getProperty(SECRET_PROPERTY);

  if (!expected) {
    return respond({ ok: false, error: 'The ' + SECRET_PROPERTY + ' script property is not set. Add it under Project Settings → Script properties.' });
  }

  if (request.secret !== expected) {
    return respond({ ok: false, error: 'Secret rejected. It must match the one saved in WestHub admin → Settings → Integrations.' });
  }

  // One writer at a time, so two submissions arriving together cannot land on
  // the same row or both create the same missing column.
  const lock = LockService.getScriptLock();

  if (!lock.tryLock(20000)) {
    return respond({ ok: false, error: 'The spreadsheet is busy. WestHub will retry automatically.' });
  }

  try {
    switch (request.action) {
      case 'ping':
        return respond(ping());
      case 'append':
        return respond(append(request));
      case 'update':
        return respond(update(request));
      default:
        return respond({ ok: false, error: 'Unknown action: ' + request.action });
    }
  } catch (err) {
    return respond({ ok: false, error: String((err && err.message) || err) });
  } finally {
    lock.releaseLock();
  }
}

/** Opening the URL in a browser confirms the deployment is live. */
function doGet() {
  return respond({ ok: false, error: 'The WestHub Sheets bridge is deployed. It only accepts POST requests from the WestHub server.' });
}

function ping() {
  const spreadsheet = SpreadsheetApp.getActiveSpreadsheet();

  return {
    ok: true,
    title: spreadsheet.getName(),
    tabs: spreadsheet.getSheets().map(function (sheet) { return sheet.getName(); }),
  };
}

/** Append one row, placing each value under its header by name. */
function append(request) {
  const sheet = sheetFor(request.tab);
  const map = headerMap(sheet, request.headers || []);
  const values = request.values || {};

  let width = 0;
  Object.keys(map).forEach(function (header) { width = Math.max(width, map[header] + 1); });

  const row = new Array(width).fill('');

  Object.keys(values).forEach(function (header) {
    if (header in map) {
      row[map[header]] = asText(values[header]);
    }
  });

  sheet.appendRow(row);

  return { ok: true, row: sheet.getLastRow() };
}

/** Update one named column on the row whose first column matches the key. */
function update(request) {
  const sheet = sheetFor(request.tab);
  const rowNumber = findRow(sheet, String(request.key));

  if (!rowNumber) {
    return { ok: true, updated: false };
  }

  const map = headerMap(sheet, request.headers || []);

  if (!(request.header in map)) {
    return { ok: true, updated: false };
  }

  sheet.getRange(rowNumber, map[request.header] + 1).setValue(asText(request.value));

  return { ok: true, updated: true, row: rowNumber };
}

/** The named tab, created when it does not exist yet. */
function sheetFor(name) {
  if (!name) {
    throw new Error('No tab name was sent.');
  }

  const spreadsheet = SpreadsheetApp.getActiveSpreadsheet();

  return spreadsheet.getSheetByName(name) || spreadsheet.insertSheet(name);
}

/**
 * Header name → zero-based column index. Writes the header row on an empty
 * tab, and appends any expected column that someone deleted, so staff can
 * reorder or add their own columns without breaking the sync.
 */
function headerMap(sheet, contract) {
  const lastColumn = sheet.getLastColumn();
  let existing = lastColumn > 0
    ? sheet.getRange(1, 1, 1, lastColumn).getDisplayValues()[0].map(function (value) { return String(value).trim(); })
    : [];

  if (existing.every(function (header) { return header === ''; })) {
    if (contract.length > 0) {
      sheet.getRange(1, 1, 1, contract.length).setValues([contract.map(asText)]);
    }
    existing = contract.slice();
  }

  // Object.create(null): a plain {} would claim to already contain headers
  // such as "constructor".
  const map = Object.create(null);

  existing.forEach(function (header, index) {
    if (header !== '' && !(header in map)) {
      map[header] = index;
    }
  });

  const missing = contract.filter(function (header) { return !(header in map); });

  if (missing.length > 0) {
    let start = existing.length;
    while (start > 0 && existing[start - 1] === '') {
      start--;
    }

    sheet.getRange(1, start + 1, 1, missing.length).setValues([missing.map(asText)]);
    missing.forEach(function (header, offset) { map[header] = start + offset; });
  }

  return map;
}

function findRow(sheet, key) {
  const lastRow = sheet.getLastRow();

  if (lastRow < 1) {
    return null;
  }

  const column = sheet.getRange(1, 1, lastRow, 1).getDisplayValues();

  for (let i = 0; i < column.length; i++) {
    if (String(column[i][0]) === key) {
      return i + 1;
    }
  }

  return null;
}

/**
 * A leading apostrophe makes Sheets keep the text exactly as sent, the same
 * as the Sheets API's RAW mode: "=HYPERLINK(...)" typed into an application
 * stays text instead of running, "+1 224..." is not parsed as a sum, and
 * "007" keeps its zeros. The apostrophe itself is not stored in the cell.
 */
function asText(value) {
  const text = value === null || value === undefined ? '' : String(value);

  return text === '' ? '' : "'" + text;
}

function respond(body) {
  return ContentService
    .createTextOutput(JSON.stringify(body))
    .setMimeType(ContentService.MimeType.JSON);
}
