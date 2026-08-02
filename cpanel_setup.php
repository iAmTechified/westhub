<?php
/**
 * WestHub cPanel Setup Script (Split Architecture Version)
 * 
 * Instructions:
 * Upload this file to your public_html folder and visit it in your browser.
 */

$password = 'westhub123!';

if (!isset($_GET['pwd']) || $_GET['pwd'] !== $password) {
    die("Unauthorized. Please append ?pwd=yourpassword to the URL.");
}

echo "<h1>WestHub cPanel Setup (Split Architecture)</h1>";
echo "<pre>";

// The absolute path to your main site's actual storage folder
// Based on your errors, your main code is in /home/westgpac/westhub/
$storageTarget = realpath(__DIR__ . '/../westhub/storage/app/public');

if (!$storageTarget) {
    die("ERROR: Could not find the main storage folder at ../westhub/storage/app/public.\nDid you upload the westhub folder correctly?");
}

// 1. Create Symlink for the MAIN website (in public_html)
echo "<h2>1. Creating Main Website Storage Symlink...</h2>";
$mainLink = __DIR__ . '/storage';

if (file_exists($mainLink) || is_link($mainLink)) {
    unlink($mainLink);
    echo "Deleted old main symlink.\n";
}

symlink($storageTarget, $mainLink);
echo "Created Main Symlink: {$mainLink} -> {$storageTarget}\n";

// 2. Create Symlink for the ADMIN website
echo "<h2>2. Creating Admin Website Storage Symlink...</h2>";
// Replace this with the actual path to your admin subdomain's public folder
$adminLink = __DIR__ . '/../admin.westhub.com/public/storage'; 

if (file_exists(dirname($adminLink))) {
    if (file_exists($adminLink) || is_link($adminLink)) {
        unlink($adminLink);
        echo "Deleted old admin symlink.\n";
    }
    
    symlink($storageTarget, $adminLink);
    echo "Created Admin Symlink: {$adminLink} -> {$storageTarget}\n";
} else {
    echo "SKIPPED: Could not find the admin public folder at " . dirname($adminLink) . ".\n";
    echo "Please update the \$adminLink variable on line 38 of this script to match your actual admin folder name.\n";
}

echo "</pre>";
echo "<h2>Setup Complete!</h2>";
echo "<p style='color:red;'><strong>CRITICAL: Delete this file from your server immediately!</strong></p>";
