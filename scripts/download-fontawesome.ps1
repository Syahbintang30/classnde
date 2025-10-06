# Download Font Awesome 7 (CSS + webfonts) to public/vendor/fontawesome
# Usage: Open PowerShell in project root and run: .\scripts\download-fontawesome.ps1

$base = Join-Path $PSScriptRoot "..\public\vendor\fontawesome"
$cssDir = Join-Path $base "css"
$webfontsDir = Join-Path $base "webfonts"

New-Item -ItemType Directory -Force -Path $cssDir | Out-Null
New-Item -ItemType Directory -Force -Path $webfontsDir | Out-Null

# Download CSS
$cssUrl = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css'
$cssOut = Join-Path $cssDir 'all.min.css'
Invoke-WebRequest -Uri $cssUrl -OutFile $cssOut

# Parse CSS to find font file URLs (woff, woff2, ttf, eot)
$cssContent = Get-Content -Raw -Path $cssOut

# Find absolute font URLs in CSS (rare) and relative webfonts references
$absoluteRegex = 'https?://[^\")]+\.(woff2|woff|ttf|eot)'
$relativeRegex = 'webfonts/([^\")]+\.(woff2|woff|ttf|eot))'

$absoluteMatches = [System.Text.RegularExpressions.Regex]::Matches($cssContent, $absoluteRegex) | ForEach-Object { $_.Value }
$relativeMatches = [System.Text.RegularExpressions.Regex]::Matches($cssContent, $relativeRegex) | ForEach-Object { $_.Groups[1].Value }

# Build final list of URLs; for relative references, use the CDN webfonts base path matching the CSS version
$cdnBase = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/webfonts/'
$fontUrls = @()

foreach ($m in $absoluteMatches) { if ($m) { $fontUrls += $m } }
foreach ($f in $relativeMatches) { if ($f) { $fontUrls += ($cdnBase + $f) } }

$fontUrls = $fontUrls | Select-Object -Unique

foreach ($url in $fontUrls) {
    try {
        $fileName = Split-Path $url -Leaf
        $outPath = Join-Path $webfontsDir $fileName
        Write-Host "Downloading $url -> $outPath"
        Invoke-WebRequest -Uri $url -OutFile $outPath
    } catch {
        # Use formatted message to avoid expansion issues
        Write-Warning ("Failed to download {0}: {1}" -f $url, $_.Exception.Message)
    }
}

Write-Host ("Font Awesome download complete. Local assets: {0}" -f $base)
