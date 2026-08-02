# PowerShell Script to Package Westhub for Deployment
# Excludes node_modules, .git, tests, and storage/framework temp files.

$zipFileName = "westhub_deployment.zip"
$sourcePath = "."
$destinationPath = "..\" + $zipFileName

Write-Host "Cleaning up old zip if it exists..." -ForegroundColor Cyan
if (Test-Path $destinationPath) { Remove-Item $destinationPath }

Write-Host "Packaging project (excluding dev folders)..." -ForegroundColor Cyan

# Define exclusions
$excludeList = @(
    "node_modules",
    ".git",
    "tests",
    ".env",
    "westhub_deployment.zip",
    "storage/framework/cache/data/*",
    "storage/framework/sessions/*",
    "storage/framework/views/*",
    "storage/logs/*",
    "westhub-admin/node_modules",
    "westhub-admin/.git",
    "westhub-admin/tests",
    "westhub-admin/.env",
    "westhub-admin/storage/framework/cache/data/*",
    "westhub-admin/storage/framework/sessions/*",
    "westhub-admin/storage/framework/views/*",
    "westhub-admin/storage/logs/*"
)

# Create the zip
Get-ChildItem -Path $sourcePath -Recurse | Where-Object { 
    $path = $_.FullName.Replace((Get-Item .).FullName + "\", "")
    $match = $false
    foreach ($exclude in $excludeList) {
        if ($path -like "$exclude*") { $match = $true; break }
    }
    -not $match
} | Compress-Archive -DestinationPath $destinationPath

Write-Host "Success! Your deployment package is ready at: $destinationPath" -ForegroundColor Green
Write-Host "Remember to copy .env.production and rename it to .env on the server." -ForegroundColor Yellow
