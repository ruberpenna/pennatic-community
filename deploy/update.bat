@echo off
rem Production update: maintenance on -> pull -> packages -> front-end build
rem -> migrate -> caches -> restart php workers -> maintenance off.
rem Run from an elevated (administrator) command prompt.
rem Keep this file ASCII-only with CRLF line endings; cmd.exe misparses UTF-8 text.

cd /d "%~dp0.."
set "PATH=G:\php\php-8.4.26;G:\php;C:\Program Files\nodejs;%PATH%"

php artisan down --retry=30 || goto :error
git pull --ff-only || goto :up_error
call composer install --no-dev --optimize-autoloader --no-interaction || goto :up_error
call npm ci --no-audit --no-fund || goto :up_error
call npm run build || goto :up_error
php artisan migrate --force || goto :up_error
php artisan posts:prune-images || goto :up_error
php artisan optimize || goto :up_error
php artisan filament:optimize || goto :up_error
nssm restart penna-php-1 || goto :up_error
nssm restart penna-php-2 || goto :up_error
php artisan up || goto :error
echo.
echo Update finished.
exit /b 0

:up_error
php artisan up
:error
echo.
echo Update failed. Check the messages above.
exit /b 1
