# Penna backup: database dump + uploaded files.
# Runs daily from Task Scheduler (see deploy\register-backup-task.ps1 and deploy\README.md).
#   Manual run: powershell -ExecutionPolicy Bypass -File G:\penna\deploy\backup.ps1
param(
    [string]$BackupDir = 'G:\penna_backups',          # where backups are kept
    [int]$KeepDays = 30,                               # older DB dumps are deleted (the newest 7 are always kept)
    # Second copy (OneDrive: another disk, synced to the cloud). Empty to skip. A failure here does not fail the backup.
    [string]$MirrorDir = 'D:\OneDrive\DEV\penna_backup\auto',
    [string]$Php = 'G:\php\php-8.4.26\php.exe'
)

$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
$logFile = Join-Path $root 'storage\logs\backup.log'

function Write-Log([string]$msg) {
    $line = '{0} {1}' -f (Get-Date -Format 'yyyy-MM-dd HH:mm:ss'), $msg
    Add-Content -Path $logFile -Value $line -Encoding UTF8
    Write-Output $line
}

$dbDir = Join-Path $BackupDir 'db'
$filesDir = Join-Path $BackupDir 'files'
New-Item -ItemType Directory -Force $dbDir, $filesDir | Out-Null

try {
    # 1. Database (consistent snapshot, gzip SQL; restore with: php artisan db:restore-sql <file>)
    $dumpFile = Join-Path $dbDir ('penna_{0}.sql.gz' -f (Get-Date -Format 'yyyyMMdd_HHmmss'))
    Push-Location $root
    try {
        # No 2>&1 here: in Windows PowerShell, redirected stderr becomes a terminating error under -ErrorAction Stop.
        $output = & $Php artisan db:backup-sql $dumpFile
        if ($LASTEXITCODE -ne 0) { throw "db:backup-sql failed (exit $LASTEXITCODE): $output" }
    } finally {
        Pop-Location
    }
    Write-Log "DB backup: $dumpFile ($([math]::Round((Get-Item $dumpFile).Length / 1KB)) KB)"

    # 2. Uploaded files: attachments (private) and avatars/editor images/notice images (public).
    #    Only changed files are copied; files deleted from the site are kept in the backup.
    foreach ($part in 'private', 'public') {
        $source = Join-Path $root "storage\app\$part"
        if (Test-Path $source) {
            robocopy $source (Join-Path $filesDir $part) /E /XO /XF .gitignore /XD livewire-tmp /R:2 /W:5 /NP /NFL /NDL /NJH /NJS | Out-Null
            if ($LASTEXITCODE -ge 8) { throw "robocopy $part failed (exit $LASTEXITCODE)" }
        }
    }
    Write-Log "Files copied: $filesDir"

    # 3. Remove old DB dumps (always keep the newest 7)
    $old = Get-ChildItem $dbDir -Filter '*.sql.gz' | Sort-Object LastWriteTime -Descending |
        Select-Object -Skip 7 | Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-$KeepDays) }
    foreach ($f in $old) { Remove-Item $f.FullName -Force; Write-Log "Old backup removed: $($f.Name)" }
}
catch {
    Write-Log "BACKUP FAILED: $_"
    exit 1
}

# 4. Second copy
if ($MirrorDir) {
    try {
        New-Item -ItemType Directory -Force $MirrorDir | Out-Null
        robocopy $BackupDir $MirrorDir /MIR /R:2 /W:5 /NP /NFL /NDL /NJH /NJS | Out-Null
        if ($LASTEXITCODE -ge 8) { throw "robocopy exit $LASTEXITCODE" }
        Write-Log "Mirrored to $MirrorDir"
    }
    catch {
        Write-Log "Mirror failed (backup itself is fine): $_"
    }
}

exit 0
