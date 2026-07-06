# Pull the server-owned trees (content/, media/uploads/, storage/versions/)
# down into a dated local backup. Run before doing template work locally,
# and monthly as an off-server backup. The SERVER is the source of truth
# for content after launch — never deploy local content over it.
#
# Usage: powershell -File scripts\pull-content.ps1
# Requires WinSCP (winget install WinSCP). TODO: fill in HOST/USER.

$stamp = Get-Date -Format 'yyyy-MM-dd'
$dest = Join-Path $PSScriptRoot "..\backups\$stamp"
New-Item -ItemType Directory -Force $dest | Out-Null

$script = @"
open ftpes://USER@HOST/ -explicit
option batch abort
option confirm off
lcd $dest
cd /public_html
get -neweronly content/ media/uploads/ storage/versions/ .
exit
"@

$tmp = New-TemporaryFile
Set-Content $tmp $script -Encoding ascii
& winscp.com /ini=nul /script=$tmp
Remove-Item $tmp
Write-Host "Pulled server content into $dest"
