# Penna 커뮤니티 Laravel 재개발 계획

작성일: 2026-10-13
대상: `comm/` 디렉터리의 기존 PHP 커뮤니티 (순수 PHP + PDO, 약 16,000줄)

---

## 1. 목표

- 기존 기능을 **Laravel 기반으로 다시 구현**하고, 보안 문제(SQL 인젝션, 평문 비밀번호, XSS, CSRF, 업로드 취약점)를 구조적으로 없앤다.
- 기존 DB 데이터(회원, 게시글, 댓글, 첨부파일, 쪽지 등)는 **이관**한다.
- 1인 개발로 유지보수 가능한 구조를 만든다. 기능은 프레임워크 기본 기능을 최대한 활용한다.

## 2. 기술 스택

| 영역 | 선택 | 이유 |
|---|---|---|
| 언어 / 프레임워크 | PHP 8.3+, Laravel 최신 안정 버전 | 인증, ORM, 검증, CSRF, 메일, 큐가 기본 제공됨 |
| DB | MySQL 8 (또는 MariaDB 10.11+), `utf8mb4` | 기존 MySQL과 호환되고, 이모지까지 지원 |
| 화면 | Blade + Livewire + Tailwind CSS | jQuery 경험을 살리기 쉽고, 별도 SPA 빌드 없이 동적 UI 구현 가능 |
| 인증 | Laravel Breeze (Livewire 스택) | 가입, 로그인, 이메일 인증, 비밀번호 재설정이 바로 제공됨 |
| 관리자 | Filament | 회원, 게시판, 파일, 공지 관리 CRUD를 빠르게 구성 |
| 에디터 | Tiptap 또는 Summernote 최신 버전 + `mews/purifier` | 리치 텍스트 HTML은 서버에서 반드시 정화해서 저장 |
| 실시간 채팅 | (보류) 추후 Laravel Reverb | 이번 범위에서 제외 |
| 메일 | Laravel Mail (SMTP) | PHPMailer 5.2.14 제거 |
| 파일 | Laravel Storage (`local` 비공개 디스크 → 추후 S3 호환 스토리지) | 업로드 파일이 웹에서 직접 실행되지 않게 함 |
| 테스트 | Pest | 핵심 흐름 Feature 테스트 |
| 개발 환경 | Laravel Herd(Windows) 또는 Sail(Docker) | 로컬 PHP·MySQL 설정 단순화 |

## 3. 기존 기능 목록과 이관 방침

### 3.1 기능 매핑

| 기존 기능 | 기존 파일 | Laravel 구현 |
|---|---|---|
| 회원가입 (아이디·닉네임 중복 체크, 프로필 이미지, 자기소개) | `register.php`, `register_ok.php` | Breeze 가입 폼 확장 + FormRequest 검증 |
| 로그인 / 로그아웃 | `login_ok.php`, `login_out.php` | Breeze |
| 비밀번호 찾기 (메일 인증번호) | `sendmail.php`, `pwd_change.php` | Breeze 비밀번호 재설정 링크로 대체 |
| 마이페이지 (정보 수정, 탈퇴) | `mypage*.php` | Profile 페이지 확장, 탈퇴는 soft delete |
| 내 글 검색 | `mysearch.php` | 게시글 목록에 "작성자=나" 필터 |
| 카테고리 (폴더 > 그룹 2단계) | `admin/admin_category*.php`, `sidebar.php` | `board_folders` / `boards` 테이블 + Filament 관리 |
| 게시판 목록 / 검색 / 페이징 | `board/board.php`, `board_search.php` | Livewire 목록 컴포넌트, `paginate()` |
| 글쓰기 / 수정 / 삭제 / 선택 삭제 | `board_write*`, `board_edit*`, `board_del`, `board_choice_del` | Resource 컨트롤러 + Policy |
| 첨부파일 업로드 / 다운로드 | `board_write_ok.php`, `download.php` | Storage 비공개 디스크 + 다운로드 라우트(권한 체크) |
| 조회수 (쿠키로 중복 방지) | `board_detail.php` | 세션 기반 중복 방지 |
| 게시글 추천 / 비추천 | `board_recomm.php` | `post_votes` 테이블, (회원, 글) 유니크 키 |
| 댓글 작성 / 수정 / 삭제 / 대댓글 | `comm_ok`, `comm_edit_ok`, `comm_del` | `comments` 테이블(`parent_id`), Livewire |
| 댓글 추천 / 비추천 | `comm_recomm.php` | `comment_votes` 테이블 |
| 공지사항 | `board/notice*.php` (`tbl_main`) | `notices` 테이블, 관리자만 작성 |
| 쪽지 (보내기, 받은/보낸 함, 읽음 표시, 답장, 선택 삭제) | `msg/*.php` | `messages` 테이블, 보낸 사람/받은 사람 각각 삭제 플래그 |
| 개인 메모 | `memo/*.php` | `memos` 테이블, 본인만 접근(Policy) |
| 출석 체크 | `board/attendance*.php` | `attendances` 테이블, (회원, 날짜) 유니크 키 |
| 실시간 채팅 | `chatting/*` | Reverb 채널 + `chat_messages` 테이블 |
| 운영자 문의 (메일 / 쪽지) | `host/hostpage.php`, `host_sendmail.php` | 문의 폼 → 운영자에게 메일 + 쪽지 |
| 관리자: 회원 관리 (검색, 정지, 복구, 삭제) | `admin/admin_member*.php` | Filament `UserResource` |
| 관리자: 파일 관리 | `admin/admin_file*.php` | Filament `AttachmentResource` |
| 회원 등급(엠블럼) | `mem_emblem` | `users.level` + 등급별 아이콘 표시 |

### 3.2 DB 스키마 재설계

기존 스키마는 코드에서 역추적한 것이다. 실제 DB를 `mysqldump --no-data`로 받아 확인한 뒤 확정한다.

| 기존 | 신규 | 주요 변경 |
|---|---|---|
| `tbl_member` | `users` | `mem_pwd` 평문 → bcrypt/argon2 해시. `mem_status`(0 탈퇴, 1 회원, 2 운영자) → `role` + `deleted_at`. 이메일 1·2 분리 입력 → 단일 `email` |
| `tbl_board` 중 `board_status = 3` 행 (카테고리 용도) | `board_folders`, `boards` | 게시글 테이블에 섞여 있던 카테고리를 별도 테이블로 분리 |
| `tbl_board` (나머지) | `posts` | 폴더·그룹 문자열 → `board_id` FK. `fk_mem_id`(아이디 문자열) → `user_id` FK. 상태값 → `deleted_at` |
| `tbl_file` | `attachments` | 원본 파일명은 표시용으로만 저장하고, 저장 파일명은 UUID 사용 |
| `tbl_board_recomm` | `post_votes` | `(user_id, post_id)` 유니크, `value` = +1 / -1 |
| `tbl_comment` | `comments` | `parent_id`로 대댓글 |
| `tbl_comment_recomm` | `comment_votes` | 위와 동일 |
| `tbl_main` | `notices` | |
| `tbl_message` | `messages` | `sender_id`, `recipient_id` FK, `read_at`, `sender_deleted_at`, `recipient_deleted_at` |
| `tbl_memo` | `memos` | |
| `tbl_attend` | `attendances` | `(user_id, date)` 유니크 |
| `tbl_chat` / `chat` | `chat_messages` | MyISAM → InnoDB |

공통 규칙:
- 모든 테이블 InnoDB + `utf8mb4`, 외래 키 사용.
- 회원 참조는 아이디 문자열이 아니라 `user_id`(정수 FK)로 한다.
- 상태 숫자 코드(`status = 0/1/2/3`) 대신 `deleted_at`(soft delete)과 명시적 enum 컬럼을 쓴다.

### 3.3 데이터 이관

1. 운영 DB를 덤프하고 로컬 MySQL에 `legacy` 스키마로 복원한다.
2. `php artisan legacy:import` 커맨드를 하나 만들어 테이블 순서대로 옮긴다.
   순서: users → board_folders/boards → posts → attachments → comments → votes → notices → messages → memos → attendances
3. 비밀번호: 기존 값이 평문이라 이관할 때 바로 해시로 바꿀 수 있다. 다만 기존 비밀번호가 이미 노출됐을 수 있으므로 **첫 로그인 때 비밀번호 변경을 강제**한다(`password_must_change` 플래그).
4. 첨부파일: `comm/files/*`를 `storage/app/private/attachments/{uuid}`로 복사하고 경로를 매핑한다.
5. 게시글·댓글 본문 HTML은 이관할 때 HTMLPurifier로 한 번 정화한다.
6. 이관 후 건수 검증 스크립트로 테이블별 행 수와 샘플 데이터를 비교한다.

## 4. 보안 기준 (처음부터 적용)

- 모든 DB 접근은 Eloquent / Query Builder로 한다. 원시 SQL에 변수를 이어 붙이지 않는다.
- Blade 출력은 `{{ }}`만 쓴다. `{!! !!}`는 정화된 본문 HTML에만 허용한다.
- 상태를 바꾸는 요청은 모두 POST/PATCH/DELETE + CSRF 토큰. GET으로 삭제·변경하지 않는다.
- 권한 체크는 Policy와 Gate로 한다(본인 글만 수정, 운영자만 관리자 페이지 접근).
- 업로드: 확장자와 MIME 화이트리스트, 크기 제한, 비공개 디스크에 UUID 파일명으로 저장, 다운로드는 컨트롤러를 거친다.
- 로그인, 가입, 쪽지, 댓글에 Rate limiting을 건다.
- `.env`로 비밀값을 분리하고 저장소에 커밋하지 않는다. 운영 환경은 `APP_DEBUG=false`.
- DB 포트(3306)는 외부에 공개하지 않는다.

## 5. 단계별 일정 (1인, 주 10–15시간 기준 추정)

| 단계 | 내용 | 완료 기준 | 예상 기간 |
|---|---|---|---|
| 0. 정리 | 기존 DB 비밀번호 교체, 3306 차단, 운영 중이면 서비스 내리기. 실제 스키마 덤프. 레거시 코드는 `legacy/`로 옮기고 Git 저장소 정리 | 노출된 비밀번호 무효화, 스키마 파일 확보 | 1주 |
| 1. 기반 | Laravel 프로젝트 생성, Breeze, Tailwind, Pest, 마이그레이션 전체 작성, 시더/팩토리 | `php artisan migrate --seed`로 더미 데이터가 들어간 사이트가 뜸 | 1–2주 |
| 2. 회원 | 가입(닉네임, 프로필 이미지, 자기소개), 로그인, 이메일 인증, 비밀번호 재설정, 마이페이지, 탈퇴 | 회원 흐름 Feature 테스트 통과 | 1–2주 |
| 3. 게시판 핵심 | 폴더/그룹 사이드바, 목록, 검색, 페이징, 글쓰기·수정·삭제, 에디터, 첨부파일, 조회수 | 글 CRUD와 권한 테스트 통과 | 2–3주 |
| 4. 상호작용 | 댓글·대댓글, 글·댓글 추천/비추천, 공지사항, 내 글 보기 | | 1–2주 |
| 5. 개인 기능 | 쪽지, 메모, 출석 체크, 운영자 문의 | | 1–2주 |
| 6. 관리자 | Filament: 회원, 카테고리, 게시글, 첨부파일, 공지 관리 | 운영자 계정으로 모든 관리 작업 가능 | 1주 |
| 8. 이관 | `legacy:import` 커맨드, 파일 복사, 건수 검증 | 운영 데이터 사본으로 이관 리허설 성공 | 1–2주 |
| 9. 배포 | 이 PC에 PHP·Composer 설치, nginx + php-cgi 서비스 등록, 큐 워커(NSSM), HTTPS, DB 백업 스케줄 | 공인 IP/도메인으로 접속 가능 | 1주 |

합계는 채팅을 빼고 약 **3개월**이다. 2–5단계가 끝나면 관리자 화면 없이도 기본 커뮤니티로 먼저 공개할 수 있다(MVP).

## 6. 결정 사항 (2026-10-13 확정)

1. **운영 데이터 이관: 한다.** 기존 MySQL(`myhome`)은 유지되고 있으며, 접속 주소만 현재 공인 IP(59.x.x.x)로 바뀌었다. 접속 정보는 `.env`의 `LEGACY_DB_*`로 분리하고 코드에는 넣지 않는다.
2. **화면 방식: Blade + Livewire.**
3. **소스 관리 / 배포: GitHub + 이 PC(Windows)의 nginx.**
   - 저장소는 GitHub **private**로 만든다. `.env`, `comm.zip`, 업로드 파일, 레거시 `dbconfig.php`는 커밋하지 않는다.
   - 웹 서버는 `G:
ginx
ginx-1.29.1`을 쓴다. 지금 설정은 기본값(PHP 미연결)이므로 다음이 필요하다.
     - PHP 8.3+ (Windows NTS 빌드) 설치 → `php-cgi.exe -b 127.0.0.1:9000`을 NSSM으로 서비스 등록
     - nginx `root`를 Laravel `public/`으로 지정하고 `try_files $uri $uri/ /index.php?$query_string;` + `fastcgi_pass 127.0.0.1:9000;` 설정
     - nginx도 NSSM으로 서비스 등록
     - Composer 설치
   - 같은 PC에서 `blog-caddy` 서비스가 8123 포트를 쓰고 있으므로 포트가 겹치지 않게 한다(nginx 80/443).
   - 배포 절차: `git pull` → `composer install --no-dev` → `php artisan migrate --force` → `php artisan optimize`.
4. **채팅: 보류.** 7단계는 이번 범위에서 뺀다. 기존 `tbl_chat` 데이터는 이관하지 않고 덤프만 보관한다.
5. **엠블럼(`mem_emblem`): 회원 등급으로 본다.** `users.level`(정수)로 옮기고, 이관 전에 실제 값 분포(`SELECT mem_emblem, COUNT(*) ... GROUP BY`)를 확인해 등급 표를 확정한다.

## 7. 다음 작업

- [ ] 0단계: DB 비밀번호 교체, DB 서버 3306 포트를 허용 IP로만 제한
- [ ] 0단계: 실제 스키마 덤프(`mysqldump --no-data myhome > legacy-schema.sql`) 후 3.2 확정
- [ ] 0단계: GitHub private 저장소 생성, `.gitignore` 정리, 레거시 코드는 `legacy/`로 이동
- [ ] 1단계: 이 PC에 PHP 8.3+ / Composer 설치, Laravel 프로젝트 생성과 마이그레이션 작성
