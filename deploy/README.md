# Penna 운영 서버 (Windows)

도메인: `https://myhome.pennatic.info` (Cloudflare 프록시 사용)

```
인터넷 → Cloudflare → 공유기(80/443) → Nginx Proxy Manager(HTTPS, 192.168.0.33)
       → nginx(이 PC 192.168.0.24:8765) → php-cgi 2개(127.0.0.1:9101, 9102)
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
   - Domain: 사이트 도메인 / Scheme: `http` / Forward IP: `192.168.0.24` / Port: `8765`
   - Websockets Support는 꺼도 됩니다.
   - SSL 탭: Let's Encrypt 발급, Force SSL 켜기
   - Advanced 탭: 아래 내용 (첨부파일 업로드 크기, Cloudflare 뒤의 실제 방문자 IP)
     ```nginx
     client_max_body_size 60m;
     real_ip_header CF-Connecting-IP;
     # https://www.cloudflare.com/ips/ 의 IP 대역을 set_real_ip_from 으로 모두 추가
     ```
     실제 IP를 넘기지 않으면 로그인 실패 제한 등이 Cloudflare 서버 IP 단위로 묶입니다.
   - Cloudflare SSL/TLS 모드는 Full (strict)를 권장합니다.
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

## 메일 발송 (네이버 SMTP)

회원가입 인증 메일과 비밀번호 재설정 메일에 씁니다.

1. 네이버 메일 → 환경설정 → **POP3/IMAP 설정** → **POP3/SMTP 사용: 사용함**
2. 네이버 2단계 인증을 쓰고 있다면 네이버 계정 → 보안 설정 → **애플리케이션 비밀번호**를 하나 만듭니다.
3. `G:\penna\.env`의 `MAIL_PASSWORD=` 뒤에 비밀번호(또는 애플리케이션 비밀번호)를 넣습니다.
   `MAIL_USERNAME`, `MAIL_FROM_ADDRESS`는 보내는 네이버 메일 주소와 같아야 합니다.
4. 관리자 권한 명령 프롬프트에서 적용합니다.
   ```bat
   cd /d G:\penna
   php artisan optimize
   nssm restart penna-php-1
   nssm restart penna-php-2
   ```

메일 발송이 실패해도 가입은 완료되고, 회원은 인증 화면에서 메일을 다시 받을 수 있습니다. 실패 기록은 `storage\logs`에 남습니다.

## DB 자동 백업

매일 04:30에 작업 스케줄러(`penna-backup`, SYSTEM 계정)가 `deploy\backup.ps1`을 실행합니다.

| 항목 | 내용 |
|---|---|
| DB | `G:\penna_backups\db\penna_날짜_시간.sql.gz` (30일 보관, 최소 최근 7개는 항상 유지) |
| 업로드 파일 | `G:\penna_backups\files\private`(첨부파일), `files\public`(프로필·본문 이미지) — 바뀐 파일만 복사 |
| 두 번째 사본 | `D:\OneDrive\DEV\penna_backup\auto` (다른 디스크 + OneDrive 동기화) |
| 기록 | `G:\penna\storage\logs\backup.log` |

- 작업 등록(처음 한 번, 관리자 PowerShell): `powershell -ExecutionPolicy Bypass -File G:\penna\deploy\register-backup-task.ps1`
- 바로 백업: `powershell -ExecutionPolicy Bypass -File G:\penna\deploy\backup.ps1`

### 복원

1. `php artisan down`
2. DB: `php artisan db:restore-sql G:\penna_backups\db\penna_….sql.gz` (백업에 있는 테이블을 모두 교체합니다)
3. 파일: `G:\penna_backups\files\private`, `files\public`을 `G:\penna\storage\app\` 아래로 복사
4. `php artisan up`
