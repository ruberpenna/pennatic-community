# Registers a daily 04:30 Task Scheduler job that runs deploy\backup.ps1 (run in an elevated PowerShell).
# Runs as SYSTEM so it works without anyone logged in. Re-running replaces the existing task.
$ErrorActionPreference = 'Stop'
$script = Join-Path $PSScriptRoot 'backup.ps1'

$action = New-ScheduledTaskAction -Execute 'powershell.exe' `
    -Argument "-NoProfile -ExecutionPolicy Bypass -File `"$script`""
$trigger = New-ScheduledTaskTrigger -Daily -At 4:30
$settings = New-ScheduledTaskSettingsSet -StartWhenAvailable -ExecutionTimeLimit (New-TimeSpan -Hours 1)
$principal = New-ScheduledTaskPrincipal -UserId 'SYSTEM' -LogonType ServiceAccount -RunLevel Highest

Register-ScheduledTask -TaskName 'penna-backup' -Action $action -Trigger $trigger `
    -Settings $settings -Principal $principal -Force | Out-Null

# Run once now so the first backup exists immediately.
Start-ScheduledTask -TaskName 'penna-backup'

# Get-ScheduledTask can fail on machines with a broken task, so use schtasks to show the result.
schtasks /query /tn penna-backup /fo LIST
