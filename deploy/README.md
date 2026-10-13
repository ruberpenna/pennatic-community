# Penna 운영 서버 (Windows)

```
인터넷 → 공유기(80/443) → Nginx Proxy Manager(HTTPS, 192.168.0.33)
       → nginx(이 PC 192.168.0.24:8124) → php-cgi 2개(127.0.0.1:9101, 9102)
       → MySQL(192.168.0.27, DB penna)
```

| 항목 | 위치 / 값 |
|---|---|
| 운영 코드 | `G:\penna` (GitHub `ruberpenna/pennatic-community`의 clone) |
| 개발 코드 | `G:\pennticComm` |
| PHP / Composer | `G:\php\php-8.4.26`, `G:\php\composer.bat` |
| nginx | `G:\nginx\nginx-1.29.1` (설정은 `deploy\nginx.conf`가 복사됨, 원본은 `conf\nginx.conf.orig`) |
| 서비스 | `penna-php-1`, `penna-php-2`, `penna-nginx` (NSSM, 자동 시작) |
| 로그 | `G:\penna\storage\logs` (앱), `storage\logs\services` (서비스), `G:\nginx\nginx-1.29.1\logs` |
| DB 계정 | `penna_app@192.168.0.24` — `penna` 전체 권한, `myhome` 읽기 전용 |

## 업데이트

개발 PC에서 push한 뒤, **관리자 권한 명령 프롬프트**에서:

```bat
G:\penna\deploy\update.bat
```

점검 모드 → `git pull` → 패키지 설치 → 프론트 빌드 → 마이그레이션 → 캐시 → php-cgi 재시작 → 점검 해제 순서로 진행합니다.

## 도메인 연결 (NPM)

1. NPM에서 Proxy Host 추가
   - Domain: 사이트 도메인 / Scheme: `http` / Forward IP: `192.168.0.24` / Port: `8124`
   - Websockets Support는 꺼도 됩니다.
   - SSL 탭: Let's Encrypt 발급, Force SSL 켜기
   - Advanced 탭: `client_max_body_size 60m;` (첨부파일 업로드용)
2. `G:\penna\.env` 수정
   ```
   APP_URL=https://도메인
   SESSION_SECURE_COOKIE=true
   ```
3. 관리자 권한 명령 프롬프트에서 캐시 갱신 후 재시작
   ```bat
   cd /d G:\penna
   php artisan optimize
   nssm restart penna-php-1
   nssm restart penna-php-2
   ```

## 서비스 다시 설치

`deploy\install-services.ps1`을 관리자 권한 PowerShell에서 실행합니다. 이미 있는 서비스는 설정만 갱신됩니다.

## 레거시 데이터 다시 가져오기

`php artisan db:seed --class=LegacyImportSeeder --force` — 여러 번 실행해도 중복되지 않습니다.
