@echo off
chcp 65001 >nul
rem 운영 서버 업데이트: 최신 코드 -> 패키지 -> 프론트 빌드 -> DB 마이그레이션 -> 캐시 -> 서비스 재시작
rem 관리자 권한 명령 프롬프트에서 실행하세요
cd /d "%~dp0.."
set PATH=G:\php\php-8.4.26;G:\php;C:\Program Files\nodejs;%PATH%

php artisan down --retry=30 || goto :error
git pull --ff-only || goto :up_error
call composer install --no-dev --optimize-autoloader --no-interaction || goto :up_error
call npm ci --no-audit --no-fund || goto :up_error
call npm run build || goto :up_error
php artisan migrate --force || goto :up_error
php artisan optimize || goto :up_error
php artisan filament:optimize || goto :up_error
nssm restart penna-php-1 || goto :up_error
nssm restart penna-php-2 || goto :up_error
php artisan up || goto :error
echo.
echo 업데이트 완료
exit /b 0

:up_error
php artisan up
:error
echo.
echo 업데이트 중 오류가 발생했습니다. 위 메시지를 확인하세요.
exit /b 1
