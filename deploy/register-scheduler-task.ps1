# Registers a Task Scheduler job that runs Laravel's scheduler (php artisan schedule:run) every minute
# (run in an elevated PowerShell). The schedule itself lives in routes/console.php.
# Runs as SYSTEM so it works without anyone logged in. Re-running replaces the existing task.
param(
    [string]$AppDir = (Split-Path $PSScriptRoot -Parent),
    [string]$Php = 'G:\php\php-8.4.26\php.exe'
)
$ErrorActionPreference = 'Stop'

$action = New-ScheduledTaskAction -Execute $Php -Argument 'artisan schedule:run' -WorkingDirectory $AppDir
# Start now and repeat every minute indefinitely.
$trigger = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Minutes 1)
$settings = New-ScheduledTaskSettingsSet -StartWhenAvailable -MultipleInstances IgnoreNew -ExecutionTimeLimit (New-TimeSpan -Minutes 30)
$principal = New-ScheduledTaskPrincipal -UserId 'SYSTEM' -LogonType ServiceAccount -RunLevel Highest

Register-ScheduledTask -TaskName 'penna-scheduler' -Action $action -Trigger $trigger `
    -Settings $settings -Principal $principal -Force | Out-Null

schtasks /query /tn penna-scheduler /fo LIST
