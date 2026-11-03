# Registers Penna's Windows services (run once, in an elevated PowerShell).
#   penna-php-1, penna-php-2 : php-cgi FastCGI workers on 127.0.0.1:9101/9102
#   penna-nginx              : nginx on :8765 (Nginx Proxy Manager forwards the domain here)
# Re-running is safe: existing services are updated, not duplicated.

param(
    [string]$AppDir = 'G:\penna',
    [string]$PhpDir = 'G:\php\php-8.4.27',
    [string]$NginxDir = 'G:\nginx\nginx-1.29.1',
    [string]$ProxyIp = '192.168.0.33',
    [int]$Port = 8765
)

$ErrorActionPreference = 'Stop'

if (-not ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    throw 'Run this script in an elevated (administrator) PowerShell.'
}

$nssm = (Get-Command nssm -ErrorAction Stop).Source
$logDir = Join-Path $AppDir 'storage\logs\services'
New-Item -ItemType Directory -Force $logDir | Out-Null

function Install-PennaService([string]$Name, [string]$Exe, [string]$Arguments, [string]$Directory, [string[]]$Environment = @()) {
    if (-not (Get-Service $Name -ErrorAction SilentlyContinue)) {
        & $nssm install $Name $Exe | Out-Null
    } else {
        & $nssm stop $Name | Out-Null
        & $nssm set $Name Application $Exe | Out-Null
    }
    if ($Arguments) {
        & $nssm set $Name AppParameters $Arguments | Out-Null
    } else {
        & $nssm reset $Name AppParameters | Out-Null
    }
    & $nssm set $Name AppDirectory $Directory | Out-Null
    & $nssm set $Name AppStdout (Join-Path $logDir "$Name.log") | Out-Null
    & $nssm set $Name AppStderr (Join-Path $logDir "$Name.log") | Out-Null
    & $nssm set $Name AppRotateFiles 1 | Out-Null
    & $nssm set $Name AppRotateBytes 10485760 | Out-Null
    # G: is a USB drive that can mount after services start at boot: start delayed, and retry
    # after 60 s if the service still fails (also on error exits, not only crashes).
    & $nssm set $Name Start SERVICE_DELAYED_AUTO_START | Out-Null
    sc.exe failure $Name reset= 86400 actions= restart/60000/restart/60000/restart/60000 | Out-Null
    sc.exe failureflag $Name 1 | Out-Null
    if ($Environment.Count) {
        & $nssm set $Name AppEnvironmentExtra @Environment | Out-Null
    }
    & $nssm start $Name | Out-Null
    Write-Host "$Name : $((Get-Service $Name).Status)"
}

# nginx config (keep the original once)
$conf = Join-Path $NginxDir 'conf\nginx.conf'
if (-not (Test-Path "$conf.orig")) { Copy-Item $conf "$conf.orig" }
$template = Get-Content (Join-Path $AppDir 'deploy\nginx.conf') -Raw
[IO.File]::WriteAllText($conf, ($template -replace 'listen\s+\d+;', "listen       $Port;"))

# php-cgi exits after PHP_FCGI_MAX_REQUESTS requests and NSSM restarts it, which bounds memory growth.
# (PowerShell variable names are case-insensitive, so don't reuse $Port here.)
foreach ($workerPort in 9101, 9102) {
    $name = "penna-php-$($workerPort - 9100)"
    Install-PennaService $name (Join-Path $PhpDir 'php-cgi.exe') "-b 127.0.0.1:$workerPort" $AppDir @('PHP_FCGI_MAX_REQUESTS=1000')
}

& (Join-Path $NginxDir 'nginx.exe') -t -p $NginxDir
Install-PennaService 'penna-nginx' (Join-Path $NginxDir 'nginx.exe') '' $NginxDir

# Only Nginx Proxy Manager needs to reach the web port.
Get-NetFirewallRule -DisplayName 'Penna 8124' -ErrorAction SilentlyContinue | Remove-NetFirewallRule
$rule = 'Penna web'
Get-NetFirewallRule -DisplayName $rule -ErrorAction SilentlyContinue | Remove-NetFirewallRule
New-NetFirewallRule -DisplayName $rule -Direction Inbound -Protocol TCP -LocalPort $Port -RemoteAddress $ProxyIp -Action Allow | Out-Null
Write-Host "firewall: $rule allows $ProxyIp to port $Port"
