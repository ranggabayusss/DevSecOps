# DOKUMENTASI REPOSITORY & THREAT MODEL STRIDE — UNKNOWN RIDER WEB

**Commit yang didokumentasikan:** `ccaaf84` (`main`, merge PR #3 `security-hardening`)  
**Tanggal dokumentasi:** 22 November 2026  
**Cakupan:** seluruh file yang dilacak Git di repository ini, termasuk kode aplikasi, konfigurasi, migration, view, test, Docker, CI/CD, dokumentasi, Postman, dan tooling AI.  
**Metode:** pembacaan langsung file sumber; penomoran baris mengikuti file aktual. File biner (`.drawio`, `.pdf`, `.zip`, `.ico`) dan lockfile dependency dijelaskan secara struktural, bukan dibaca sebagai teks baris demi baris.

> Catatan penting: dokumen ini adalah hasil audit source code, bukan jaminan bahwa aplikasi bebas dari seluruh kerentanan. Status “Terimplementasi” berarti kontrol terlihat di kode dan/atau test; status “Belum Lengkap” berarti kontrol ada tetapi memiliki celah; status “Belum Ada Mitigasi” berarti tidak ditemukan kontrol yang memadai di repository.

---

## DAFTAR ISI

1. [Gambaran Umum & Tech Stack](#1-gambaran-umum--tech-stack)
2. [Struktur Direktori & Arsitektur Sistem](#2-struktur-direktori--arsitektur-sistem)
3. [Analisis STRIDE](#3-analisis-stride)
4. [Penjelasan Seluruh Kode (Baris demi Baris)](#4-penjelasan-seluruh-kode-baris-demi-baris)
5. [Kesimpulan Audit & Rekomendasi Prioritas](#5-kesimpulan-audit--rekomendasi-prioritas)

---

## 1. GAMBARAN UMUM & TECH STACK

### 1.1 Tujuan Aplikasi

**Unknown Rider Web** adalah aplikasi web berbasis Laravel untuk pengelolaan komunitas motor **Unknown Rider**. Aplikasi ini menyediakan:

- registrasi dan login anggota;
- pengelolaan profil anggota (nama, telepon, tipe motor, plat nomor);
- pembuatan komunitas oleh Admin dan penetapan Ketua komunitas;
- pengajuan bergabung anggota ke komunitas dan persetujuan/penolakan oleh Ketua;
- pengelolaan anggota oleh Admin (tambah, keluarkan, ganti Ketua);
- pembuatan acara/pengumuman oleh Admin atau Ketua;
- konfirmasi kehadiran (RSVP) anggota pada acara;
- ekspor daftar kehadiran ke file Excel `.xlsx`;
- audit trail aktivitas keamanan dan perubahan data;
- API minimal untuk membaca identitas pengguna yang terautentikasi.

Versi repository ini secara eksplisit menghapus fitur **Live GPS Tracking** dari aplikasi lama. Namun, audit menemukan bahwa klaim dokumentasi lama tentang migration penghapus tabel GPS **tidak sesuai dengan isi repository** (lihat Bagian 3 dan 4.10).

### 1.2 Aktor dan Peran

| Peran | Kode role | Hak utama |
|---|---|---|
| Admin | `admin` | Akses panel admin, kelola semua pengguna/role, buat komunitas, tunjuk/ganti Ketua, tambah/keluarkan anggota, kelola semua acara, ekspor kehadiran. |
| Ketua | `ketua` | Mengelola permintaan bergabung untuk komunitas yang dipimpinnya, membuat/mengubah/menghapus acara, ekspor kehadiran. |
| Member | `member` | Melihat komunitas, mengajukan bergabung, mengelola profil sendiri, melihat acara, melakukan RSVP. |
| Guest | — | Registrasi, login. |

### 1.3 Tech Stack dan Versi

Versi di bawah ini diambil dari `composer.json`, `composer.lock`, `package.json`, `package-lock.json`, `php artisan about`, dan pemeriksaan runtime lokal.

| Lapisan | Teknologi | Versi terpasang / constraint | Peran dalam aplikasi |
|---|---|---|---|
| Bahasa backend | PHP | `^8.3`; runtime 8.3.36 | Menjalankan Laravel, controller, model, service, migration. |
| Framework | Laravel | `^13.17`; lock `v13.40.0` | Routing, ORM, validasi, session, auth, middleware, Blade, queue, cache, testing. |
| Autentikasi API | Laravel Sanctum | `^4.3`; lock `v4.3.3` | Autentikasi token/session untuk route `/api/user`. |
| CLI interaktif | Laravel Tinker | `^3.0`; lock `v3.0.2` | REPL untuk debugging dan inspeksi model. |
| Testing | PHPUnit | `^12.5.12`; lock `12.5.43` | Unit dan feature test. |
| Data uji | FakerPHP Faker | `^1.23`; lock `v1.24.1` | Data palsu pada factory dan seeder. |
| Code style | Laravel Pint | `^1.27`; lock `v1.32.1` | Formatter PHP. |
| Log viewer | Laravel Pail | `^1.2.5`; lock `v1.2.7` | Membaca log Laravel dari CLI. |
| Dev tooling | Laravel PAO | `^1.0.6`; lock `v1.1.5` | Tooling pengembangan Laravel (dipakai `php artisan dev`). |
| Error handling test | Nuno Maduro Collision | `^8.6`; lock `v8.9.5` | Tampilan error yang lebih ramah saat test/CLI. |
| Mocking | Mockery | `^1.6`; lock `1.6.15` | Mock objek pada test. |
| Frontend build | Vite | `^8.0.0`; lock `8.3.5` | Bundling CSS/JS dan dev server. |
| Plugin Laravel Vite | laravel-vite-plugin | `^3.1`; lock `3.2.0` | Integrasi manifest Vite dengan Blade dan font Bunny. |
| CSS framework | Tailwind CSS | `^4.0.0`; lock `4.3.3` | Utility-first styling seluruh view. |
| Plugin Tailwind Vite | @tailwindcss/vite | `^4.0.0`; lock `4.3.3` | Kompilasi Tailwind melalui Vite. |
| Dev process runner | concurrently | `^10.0.3`; lock `10.0.6` | Menjalankan beberapa proses pengembangan. |
| Runtime Node | Node.js | 22.16.0 (lokal); CI Node 22 | Menjalankan Vite/npm. |
| Package manager JS | npm | 10.9.2 (lokal) | Instalasi dependency JS. |
| Database | MySQL | 8.0 (image Docker) | Penyimpanan data produksi/lokal via Docker. |
| Database test | SQLite | in-memory (`:memory:`) | Test cepat tanpa MySQL. |
| Web server | Nginx | `nginx:alpine` | Reverse proxy, static file, rate limit, security headers, penolakan path sensitif. |
| Application server | PHP-FPM | `php:8.3-fpm` | Menjalankan Laravel. |
| Kontainerisasi | Docker Compose | format Compose modern | Orkestrasi `web`, `app`, `db`. |
| CI | GitHub Actions | `ubuntu-latest` | Test, build frontend, SonarCloud. |
| CI alternatif | Jenkins | Pipeline Windows | Test Laravel di agent Windows. |
| Analisis kualitas | SonarQube Cloud | `sonar-project.properties` | Scan source dan coverage. |
| DAST | OWASP ZAP | 2.17.0 (menurut docs) | Baseline scan pasif. |
| Tooling AI | Laravel Boost, CodeGraph, OpenCode, Claude | konfigurasi di repo | Bukan runtime aplikasi; membantu pengembangan. |

### 1.4 Dependency PHP Penting (Langsung dan Transitive)

**Direct dependency produksi (`composer.json` baris 8–13):**

- `php: ^8.3` — versi minimum bahasa.
- `laravel/framework: ^13.17` — framework utama.
- `laravel/sanctum: ^4.3` — API authentication.
- `laravel/tinker: ^3.0` — REPL.

**Direct dependency pengembangan (`composer.json` baris 14–23):**

- `fakerphp/faker`, `laravel/boost`, `laravel/pail`, `laravel/pao`, `laravel/pint`, `mockery/mockery`, `nunomaduro/collision`, `phpunit/phpunit`.

**Transitive penting yang memengaruhi keamanan/runtime (dari `composer.lock`):**

| Paket | Versi | Peran |
|---|---|---|
| `brick/math` | 1.0.0 | Aritmetika presisi untuk dependency. |
| `carbonphp/carbon-doctrine-types` | 3.2.1 | Tipe Carbon untuk Doctrine. |
| `dflydev/dot-access-data` | v3.0.3 | Akses konfigurasi berbasis dot. |
| `doctrine/inflector` | 2.1.1 | Pluralisasi/inflection. |
| `doctrine/lexer` | 3.0.3 | Lexer untuk parser. |
| `dragonmantank/cron-expression` | v3.6.1 | Evaluasi ekspresi cron scheduler. |
| `egulias/email-validator` | 4.0.4 | Validasi email. |
| `fruitcake/php-cors` | v1.4.0 | Penanganan CORS (jika diaktifkan). |
| `graham-campbell/result-type` | v1.2.0 | Tipe Result. |
| `guzzlehttp/guzzle` | 8.3.1 | HTTP client (tidak dipakai untuk request user saat ini). |
| `guzzlehttp/promises` | 3.1.0 | Promise untuk Guzzle. |
| `guzzlehttp/psr7` | 3.2.0 | PSR-7 HTTP message. |
| `guzzlehttp/uri-template` | v2.0.1 | Template URI. |
| `laravel/prompts` | v0.3.26 | Prompt CLI. |
| `laravel/serializable-closure` | v2.1.0 | Serialisasi closure untuk queue. |
| `league/commonmark` | 2.10.7 | Markdown parser (dipakai framework). |
| `league/config` | v1.2.0 | Konfigurasi. |
| `league/flysystem` | 3.36.0 | Abstraksi filesystem. |
| `league/flysystem-local` | 3.35.3 | Driver filesystem lokal. |
| `league/mime-type-detection` | 1.17.0 | Deteksi MIME. |
| `league/uri` | 7.10.0 | Parsing URI. |
| `league/uri-interfaces` | 7.10.0 | Interface URI. |
| `monolog/monolog` | 3.x (transitive) | Logging. |
| `nesbot/carbon` | 3.x (transitive) | Tanggal/waktu. |
| `nunomaduro/termwind` | 2.x (transitive) | Tampilan CLI. |
| `psr/*` | berbagai | Standar PSR. |
| `ramsey/collection`, `ramsey/uuid` | berbagai | Koleksi dan UUID. |
| `symfony/*` | berbagai | Komponen HTTP, console, process, routing, var-dumper. |
| `tijsverkoyen/css-to-inline-styles` | 2.x (transitive) | Inline CSS untuk email (tidak aktif). |
| `vlucas/phpdotenv` | 5.x (transitive) | Membaca `.env`. |
| `voku/portable-ascii` | 2.x (transitive) | Konversi ASCII. |

> Lockfile mencatat 79 paket produksi dan 38 paket pengembangan. Daftar lengkap ada di `composer.lock`; dokumen ini hanya merangkum paket yang relevan.

### 1.5 Dependency JavaScript Penting

Dari `package.json` dan `package-lock.json`:

| Paket | Versi | Peran |
|---|---|---|
| `vite` | 8.3.5 | Bundler dan dev server. |
| `laravel-vite-plugin` | 3.2.0 | Menghubungkan Vite dengan Laravel, termasuk font Bunny. |
| `tailwindcss` | 4.3.3 | CSS utility. |
| `@tailwindcss/vite` | 4.3.3 | Plugin Tailwind untuk Vite. |
| `concurrently` | 10.0.6 | Menjalankan proses paralel saat development. |
| `@laravel/multiplex` | opsional, tidak terpasang | Dependency opsional Laravel. |
| `lightningcss` | 1.32.0 | CSS transformer yang dipakai Tailwind/Vite. |
| `ink`, `react`, `chalk`, `commander` | transitive | CLI tooling dari `concurrently`/Laravel. |

**Script npm (`package.json` baris 5–8):**

- `npm run build` → `vite build` (produksi).
- `npm run dev` → `vite` (dev server).

### 1.6 Infrastruktur dan Runtime

**Topologi Docker (`docker-compose.yml`):**

```
Browser
  │  HTTP 127.0.0.1:8000
  ▼
web (nginx:alpine, container unknown_rider_web)
  │  fastcgi_pass app:9000
  ▼
app (php:8.3-fpm, container unknown_rider_app)
  │  MySQL 3306 via network internal
  ▼
db (mysql:8.0, container unknown_rider_db)
```

- `web` memetakan port hanya ke `127.0.0.1:8000:80` (tidak terbuka ke LAN).
- `app` tidak mempublikasikan port; hanya di jaringan `app_network`.
- `db` tidak mempublikasikan port; hanya di jaringan `app_network`.
- Volume `dbdata` menyimpan data MySQL.
- `db` memiliki healthcheck `mysqladmin ping`; `app` menunggu `service_healthy`.
- `web` dan `app` me-mount source code; `web` read-only, `app` read-write.
- Tidak ada service queue worker, Redis, mail catcher, atau reverse proxy TLS di Compose.

**Runtime lokal (`php artisan about`):**

- Application Name: Unknown Rider
- Laravel: 13.40.0
- PHP: 8.3.36
- Composer: 2.10.3
- Environment: local
- Debug Mode: OFF
- URL: `localhost:8000`
- Timezone: UTC
- Locale: `id`
- Broadcasting: `log`
- Cache: `database`
- Database: `mysql`
- Logs: `stack` / `daily`
- Mail: `log`
- Queue: `database`
- Session: `database`

### 1.7 Environment Variables

Tabel berikut mencakup seluruh variabel di `.env.example` (72 baris) dan variabel tambahan yang dibaca kode.

| Variabel | Contoh / default | Fungsi | Catatan keamanan |
|---|---|---|---|
| `APP_NAME` | `"Unknown Rider"` | Nama aplikasi. | Tidak sensitif. |
| `APP_ENV` | `local` | Environment Laravel. | Produksi harus `production`. |
| `APP_KEY` | kosong | Kunci enkripsi Laravel. | Wajib diisi via `php artisan key:generate`; jangan commit. |
| `APP_DEBUG` | `false` | Menampilkan stack trace. | Harus `false` di produksi. |
| `APP_URL` | `http://localhost:8000` | Base URL. | Gunakan HTTPS di produksi. |
| `APP_LOCALE` | `id` | Locale aplikasi. | Tidak sensitif. |
| `APP_FALLBACK_LOCALE` | `id` | Locale cadangan. | Tidak sensitif. |
| `APP_FAKER_LOCALE` | `id_ID` | Locale Faker. | Hanya test/seeder. |
| `APP_MAINTENANCE_DRIVER` | `file` | Driver maintenance mode. | Tidak sensitif. |
| `BCRYPT_ROUNDS` | `12` | Cost hashing password. | Semakin tinggi semakin lambat; 12 wajar. |
| `LOG_CHANNEL` | `stack` | Channel log default. | Log bisa memuat data sensitif; batasi akses file. |
| `LOG_STACK` | `daily` | Channel stack. | Rotasi harian. |
| `LOG_DEPRECATIONS_CHANNEL` | `null` | Log deprecation. | Tidak sensitif. |
| `LOG_LEVEL` | `warning` | Level log minimum. | `debug` di produksi dapat membocorkan data. |
| `DB_CONNECTION` | `mysql` | Driver database. | — |
| `DB_HOST` | `db` | Host MySQL (nama service Docker). | Hanya jaringan internal. |
| `DB_PORT` | `3306` | Port MySQL. | Tidak dipublikasikan ke host. |
| `DB_DATABASE` | `rockbikers_db` | Nama database. | — |
| `DB_USERNAME` | `unknown_rider_app` | User database aplikasi. | Harus least privilege, bukan root. |
| `DB_PASSWORD` | kosong | Password user database. | Wajib diisi; Compose menolak start jika kosong. |
| `DB_ROOT_PASSWORD` | kosong | Password root MySQL. | Wajib diisi; jangan dipakai aplikasi. |
| `INITIAL_ADMIN_NAME` | `Administrator` | Nama admin awal untuk seeder. | Tidak sensitif. |
| `INITIAL_ADMIN_EMAIL` | kosong | Email admin awal. | Wajib sebelum `AdminUserSeeder`. |
| `INITIAL_ADMIN_PASSWORD` | kosong | Password admin awal. | Minimal 12 karakter; jangan commit. |
| `MEMBER_NUMBER_PREFIX` | `unrider` | Prefix nomor anggota. | Dibersihkan menjadi `[a-z0-9-]`, maksimal 20 karakter. |
| `SESSION_DRIVER` | `database` | Driver session. | Session disimpan di tabel `sessions`. |
| `SESSION_LIFETIME` | `60` | Umur session (menit). | Default config 120; `.env.example` 60. |
| `SESSION_ENCRYPT` | `true` | Enkripsi payload session. | Mitigasi pembacaan session di DB. |
| `SESSION_HTTP_ONLY` | `true` | Cookie tidak dapat dibaca JS. | Mitigasi XSS cookie theft. |
| `SESSION_SECURE_COOKIE` | dikomentari | Paksa cookie hanya HTTPS. | Otomatis `true` jika `APP_ENV=production`; jangan set `false` di produksi. |
| `SESSION_SAME_SITE` | `lax` | SameSite cookie. | Mitigasi CSRF; `lax` cukup untuk form same-site. |
| `SESSION_PATH` | `/` | Path cookie. | — |
| `SESSION_DOMAIN` | `null` | Domain cookie. | — |
| `BROADCAST_CONNECTION` | `log` | Broadcasting. | Tidak ada fitur realtime. |
| `FILESYSTEM_DISK` | `local` | Disk default. | File privat di `storage/app/private`. |
| `QUEUE_CONNECTION` | `database` | Queue driver. | Tidak ada worker di Compose; job tidak diproses otomatis. |
| `CACHE_STORE` | `database` | Cache store. | Rate limiter memakai cache ini. |
| `MEMCACHED_HOST` | `127.0.0.1` | Host Memcached. | Tidak dipakai karena cache `database`. |
| `REDIS_CLIENT` | `phpredis` | Client Redis. | Tidak ada service Redis. |
| `REDIS_HOST` | `127.0.0.1` | Host Redis. | Tidak dipakai. |
| `REDIS_PASSWORD` | `null` | Password Redis. | Tidak dipakai. |
| `REDIS_PORT` | `6379` | Port Redis. | Tidak dipakai. |
| `MAIL_MAILER` | `log` | Mailer default. | Email tidak terkirim; ditulis ke log. |
| `MAIL_SCHEME` | `null` | Skema SMTP. | — |
| `MAIL_HOST` | `127.0.0.1` | Host SMTP. | — |
| `MAIL_PORT` | `2525` | Port SMTP. | — |
| `MAIL_USERNAME` | `null` | User SMTP. | Jangan commit kredensial. |
| `MAIL_PASSWORD` | `null` | Password SMTP. | Jangan commit kredensial. |
| `MAIL_FROM_ADDRESS` | `noreply@example.test` | Alamat pengirim. | — |
| `MAIL_FROM_NAME` | `${APP_NAME}` | Nama pengirim. | — |
| `AWS_ACCESS_KEY_ID` | kosong | Kredensial AWS. | Tidak dipakai aktif; jangan commit. |
| `AWS_SECRET_ACCESS_KEY` | kosong | Kredensial AWS. | Tidak dipakai aktif; jangan commit. |
| `AWS_DEFAULT_REGION` | `ap-southeast-1` | Region AWS. | — |
| `AWS_BUCKET` | kosong | Bucket S3. | Tidak dipakai aktif. |
| `AWS_USE_PATH_STYLE_ENDPOINT` | `false` | Gaya endpoint S3. | — |
| `VITE_APP_NAME` | `${APP_NAME}` | Nama aplikasi untuk Vite. | Publik di bundle; jangan isi rahasia. |
| `SANCTUM_STATEFUL_DOMAINS` | tidak ada di `.env.example` | Domain stateful Sanctum. | Default `config/sanctum.php` baris 21–26. |
| `SANCTUM_TOKEN_PREFIX` | tidak ada di `.env.example` | Prefix token Sanctum. | Default kosong. |
| `AUTH_GUARD` | tidak ada | Guard default. | Default `web`. |
| `AUTH_PASSWORD_BROKER` | tidak ada | Broker reset password. | Default `users`; route reset tidak ada. |
| `AUTH_MODEL` | tidak ada | Model user. | Default `App\Models\User`. |
| `AUTH_PASSWORD_RESET_TOKEN_TABLE` | tidak ada | Tabel reset token. | Default `password_reset_tokens`. |
| `AUTH_PASSWORD_TIMEOUT` | tidak ada | Timeout konfirmasi password. | Default 10800 detik. |
| `DB_CACHE_CONNECTION`, `DB_CACHE_TABLE`, `DB_CACHE_LOCK_CONNECTION`, `DB_CACHE_LOCK_TABLE` | tidak ada | Konfigurasi cache database. | Default tabel `cache`/`cache_locks`. |
| `DB_QUEUE_CONNECTION`, `DB_QUEUE_TABLE`, `DB_QUEUE`, `DB_QUEUE_RETRY_AFTER` | tidak ada | Konfigurasi queue database. | Default tabel `jobs`, retry 90 detik. |
| `QUEUE_FAILED_DRIVER` | tidak ada | Driver failed job. | Default `database-uuids`. |
| `FILESYSTEM_DISK` | `local` | Disk default. | — |
| `LOG_SLACK_WEBHOOK_URL`, `LOG_SLACK_USERNAME`, `LOG_SLACK_EMOJI` | tidak ada | Log Slack. | Tidak aktif. |
| `PAPERTRAIL_URL`, `PAPERTRAIL_PORT` | tidak ada | Log Papertrail. | Tidak aktif. |
| `POSTMARK_API_KEY`, `RESEND_API_KEY`, `SES_*` | tidak ada | Layanan email. | Tidak aktif. |
| `SLACK_BOT_USER_OAUTH_TOKEN`, `SLACK_BOT_USER_DEFAULT_CHANNEL` | tidak ada | Notifikasi Slack. | Tidak aktif. |
| `MEMCACHED_PERSISTENT_ID`, `MEMCACHED_USERNAME`, `MEMCACHED_PASSWORD`, `MEMCACHED_HOST`, `MEMCACHED_PORT` | tidak ada | Memcached. | Tidak aktif. |
| `AWS_*` (S3) | tidak ada | Object storage. | Tidak aktif. |
| `DYNAMODB_*` | tidak ada | Cache DynamoDB. | Tidak aktif. |

### 1.8 Layanan Eksternal dan Integrasi

| Layanan | Status | Bukti / catatan |
|---|---|---|
| MySQL 8 | Aktif (Docker) | `docker-compose.yml` service `db`; `DB_CONNECTION=mysql`. |
| Nginx | Aktif (Docker) | `docker-compose.yml` service `web`; `docker/nginx/default.conf`. |
| PHP-FPM | Aktif (Docker) | `Dockerfile`; `fastcgi_pass app:9000`. |
| Mail | Tidak aktif secara nyata | `MAIL_MAILER=log`; tidak ada notifikasi email di kode. |
| Redis | Tidak aktif | Tidak ada service Redis; cache/session memakai database. |
| S3 | Tidak aktif | `FILESYSTEM_DISK=local`; tidak ada upload file. |
| GPS / maps | Dihapus | Tidak ada route/controller/model tracking; `Permissions-Policy: geolocation=()`. |
| Payment / SMS / OAuth | Tidak ada | Tidak ditemukan dependency atau route. |
| SonarCloud | CI | `.github/workflows/sonar.yml`. |
| OWASP ZAP | Manual/CI opsional | `docs/security/zap.yaml`; hasil tidak di-commit. |
| Laravel Boost / CodeGraph / OpenCode | Tooling pengembangan | `.mcp.json`, `boost.json`, `opencode.json`, `opencode.jsonc`. |

### 1.9 Asumsi, Batasan, dan Istilah

- **Asumsi deployment:** aplikasi berjalan di balik Nginx yang hanya menyajikan `public/`, dengan HTTPS di produksi. Jika dijalankan tanpa Nginx (mis. `php artisan serve`), sebagian kontrol (rate limit Nginx, penolakan path) tidak aktif; middleware aplikasi tetap aktif.
- **Asumsi database:** MySQL 8 dengan user aplikasi yang tidak memiliki hak `DROP`/`ALTER` di produksi. Migration dijalankan oleh proses deployment, bukan oleh user web.
- **Asumsi file:** tidak ada upload file pengguna. Kolom `logo` pada komunitas adalah string path/URL, bukan file yang diunggah; tidak ada validasi bahwa URL tersebut aman untuk diakses server (server tidak mengambilnya).
- **Istilah “baris”:** nomor baris mengacu pada file di commit `ccaaf84`. Untuk file yang berubah setelah commit, nomor dapat bergeser.
- **Istilah “mitigasi”:** kontrol teknis yang mengurangi kemungkinan/dampak ancaman, bukan jaminan eliminasi.
- **Batas audit:** tidak dilakukan pentest runtime, pengujian beban, atau analisis infrastruktur produksi. Temuan berbasis source code dan konfigurasi repository.

---

## 2. STRUKTUR DIREKTORI & ARSITEKTUR SISTEM

### 2.1 Pohon Direktori Lengkap

Pohon berikut mencakup seluruh file yang dilacak Git. File yang tidak dilacak (mis. `.env`, `vendor/`, `node_modules/`, `public/build/`, `storage/logs/*`, hasil scan) tidak dimasukkan karena bukan bagian repository.

```
unknown-rider-web-main/
├── .agents/
│   └── skills/
│       ├── deploying-to-cloud/
│       │   ├── SKILL.md
│       │   └── reference/checklists.md
│       ├── infer-conventions/
│       │   ├── SKILL.md
│       │   └── references/checklist.md
│       ├── laravel-best-practices/
│       │   ├── SKILL.md
│       │   └── rules/
│       │       ├── advanced-queries.md
│       │       ├── architecture.md
│       │       ├── blade-views.md
│       │       ├── caching.md
│       │       ├── collections.md
│       │       ├── config.md
│       │       ├── db-performance.md
│       │       ├── eloquent.md
│       │       ├── error-handling.md
│       │       ├── events-notifications.md
│       │       ├── http-client.md
│       │       ├── mail.md
│       │       ├── migrations.md
│       │       ├── queue-jobs.md
│       │       ├── routing.md
│       │       ├── scheduling.md
│       │       ├── security.md
│       │       ├── style.md
│       │       └── validation.md
│       ├── tailwindcss-development/
│       │   └── SKILL.md
│       └── testing-best-practices/
│           ├── SKILL.md
│           └── rules/
│               ├── assertions.md
│               ├── endpoint-tests.md
│               ├── finding-features.md
│               ├── isolation.md
│               ├── naming.md
│               ├── performance.md
│               ├── review.md
│               ├── security.md
│               └── test-data.md
├── .claude/
│   ├── CLAUDE.md
│   ├── settings.json
│   └── skills/                  # struktur dan isi identik dengan .agents/skills
│       ├── deploying-to-cloud/{SKILL.md,reference/checklists.md}
│       ├── infer-conventions/{SKILL.md,references/checklist.md}
│       ├── laravel-best-practices/{SKILL.md,rules/*.md}
│       ├── tailwindcss-development/SKILL.md
│       └── testing-best-practices/{SKILL.md,rules/*.md}
├── .github/
│   └── workflows/
│       ├── ci.yml
│       └── sonar.yml
├── .postman/
│   └── resources.yaml
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   └── UserController.php
│   │   │   ├── AdminController.php
│   │   │   ├── AuthController.php
│   │   │   ├── CommunityController.php
│   │   │   ├── CommunityJoinController.php
│   │   │   ├── CommunityRequestController.php
│   │   │   ├── Controller.php
│   │   │   ├── EventController.php
│   │   │   ├── EventExportController.php
│   │   │   ├── EventRsvpController.php
│   │   │   ├── MemberController.php
│   │   │   └── ProfileController.php
│   │   ├── Middleware/
│   │   │   ├── CheckRole.php
│   │   │   └── SecurityHeaders.php
│   │   └── Requests/
│   │       ├── EventRequest.php
│   │       ├── RsvpRequest.php
│   │       ├── UpdateMemberRoleRequest.php
│   │       └── UpdateProfileRequest.php
│   ├── Models/
│   │   ├── ActivityLog.php
│   │   ├── Community.php
│   │   ├── CommunityMember.php
│   │   ├── Event.php
│   │   ├── EventRsvp.php
│   │   └── User.php
│   ├── Policies/
│   │   └── EventPolicy.php
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   └── Support/
│       ├── ActivityLogger.php
│       ├── AttendanceExcelExporter.php
│       ├── MemberNumberService.php
│       ├── SpreadsheetValueSanitizer.php
│       └── XlsxWriter.php
├── bootstrap/
│   ├── app.php
│   ├── providers.php
│   └── cache/.gitignore
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php
│   ├── logging.php
│   ├── mail.php
│   ├── membership.php
│   ├── queue.php
│   ├── sanctum.php
│   ├── services.php
│   └── session.php
├── database/
│   ├── .gitignore
│   ├── factories/UserFactory.php
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2026_10_01_180013_create_communities_table.php
│   │   ├── 2026_10_01_180507_create_community_members_table.php
│   │   ├── 2026_10_01_180635_add_fields_to_users_table.php
│   │   ├── 2026_10_01_182447_create_activity_logs_table.php
│   │   ├── 2026_11_09_123319_create_personal_access_tokens_table.php
│   │   ├── 2026_11_16_200002_add_audit_request_context.php
│   │   ├── 2026_11_16_200003_allow_rejected_memberships.php
│   │   ├── 2026_11_18_000001_create_member_number_counters_table.php
│   │   ├── 2026_11_18_000002_add_member_profile_fields_to_users_table.php
│   │   ├── 2026_11_18_000003_create_events_table.php
│   │   └── 2026_11_18_000004_create_event_rsvps_table.php
│   └── seeders/
│       ├── AdminUserSeeder.php
│       └── DatabaseSeeder.php
├── docker/
│   ├── default.conf              # 0 byte (tidak dipakai)
│   └── nginx/default.conf
├── docs/
│   ├── SECURITY_STRIDE.md
│   ├── design/
│   │   ├── DFD_Unknown_Rider_Terbaru.drawio
│   │   ├── Flowchart Unknow Riders Web.drawio
│   │   ├── Report STRIDE dari Web Rockbikers.pdf
│   │   └── Use Unknow Riders Web.drawio
│   └── security/zap.yaml
├── postman/
│   ├── collections/STRIDE TEST/.resources/definition.yaml
│   └── globals/workspace.globals.yaml
├── public/
│   ├── .htaccess
│   ├── favicon.ico               # 0 byte
│   ├── index.php
│   └── robots.txt
├── resources/
│   ├── css/app.css
│   ├── js/app.js
│   └── views/
│       ├── admin/
│       │   ├── communities/{create,index,select}.blade.php
│       │   ├── dashboard.blade.php
│       │   ├── members/{create,index}.blade.php
│       │   └── users/index.blade.php
│       ├── auth/{login,register}.blade.php
│       ├── communities/index.blade.php
│       ├── components/{alert,empty-state,page-header,password-field}.blade.php
│       ├── dashboard.blade.php
│       ├── events/{_form,create,edit,index,show}.blade.php
│       ├── ketua/communities/requests/index.blade.php
│       ├── layouts/{app,guest}.blade.php
│       ├── profile/edit.blade.php
│       └── vendor/pagination/tailwind.blade.php
├── routes/
│   ├── api.php
│   ├── console.php
│   └── web.php
├── storage/
│   ├── app/.gitignore
│   ├── app/private/.gitignore
│   ├── app/public/.gitignore
│   ├── framework/.gitignore
│   ├── framework/cache/.gitignore
│   ├── framework/cache/data/.gitignore
│   ├── framework/sessions/.gitignore
│   ├── framework/testing/.gitignore
│   ├── framework/views/.gitignore
│   └── logs/.gitignore
├── tests/
│   ├── Feature/{AdminLeaderAccountTest,EventMembershipTest,ExampleTest,PasswordPolicyTest,SecurityHeadersTest,StrideSecurityTest}.php
│   ├── Unit/{ExampleTest,SpreadsheetValueSanitizerTest}.php
│   └── TestCase.php
├── .dockerignore
├── .editorconfig
├── .env.example
├── .gitattributes
├── .gitignore
├── .mcp.json
├── .npmrc
├── AGENTS.md
├── CLAUDE.md
├── Jenkinsfile
├── README.md
├── artisan
├── boost.json
├── composer.json
├── composer.lock
├── docker-compose.yml
├── Dockerfile
├── opencode.json
├── opencode.jsonc
├── opencode.zip
├── package-lock.json
├── package.json
├── phpunit.xml
├── sonar-project.properties
└── vite.config.js
```

### 2.2 Fungsi Setiap Folder dan File Utama

| Path | Fungsi |
|---|---|
| `.agents/`, `.claude/` | Instruksi tooling AI (Laravel Boost, skill, CodeGraph). Tidak dijalankan aplikasi; duplikat satu sama lain. |
| `.github/workflows/ci.yml` | CI test + build frontend di GitHub Actions. |
| `.github/workflows/sonar.yml` | Scan SonarCloud + coverage. |
| `.postman/`, `postman/` | Metadata workspace dan koleksi Postman untuk uji manual. |
| `app/Http/Controllers/` | Handler HTTP; memvalidasi input, memanggil model/service, mengembalikan view/redirect/response. |
| `app/Http/Middleware/` | `CheckRole` (RBAC) dan `SecurityHeaders` (header keamanan/CSP/HSTS/no-store). |
| `app/Http/Requests/` | FormRequest untuk validasi dan otorisasi input. |
| `app/Models/` | Model Eloquent dan relasi; `User` memakai atribut `Fillable`/`Hidden`. |
| `app/Policies/` | `EventPolicy` untuk otorisasi acara. |
| `app/Providers/` | `AppServiceProvider` mendaftarkan rate limiter. |
| `app/Support/` | Service domain: audit log, nomor anggota, sanitasi spreadsheet, ekspor XLSX, penulis ZIP/XML. |
| `bootstrap/app.php` | Konfigurasi aplikasi Laravel 13: routing, middleware global, alias, exception JSON. |
| `bootstrap/providers.php` | Daftar service provider. |
| `config/` | Konfigurasi framework dan aplikasi. |
| `database/factories/` | Factory data uji. |
| `database/migrations/` | Skema database dan backfill data. |
| `database/seeders/` | Seeder admin awal dan seeder default. |
| `docker/nginx/default.conf` | Konfigurasi Nginx: rate limit, security headers, penolakan path, FastCGI. |
| `docs/SECURITY_STRIDE.md` | Narasi STRIDE lama; sebagian klaim tidak lagi cocok dengan kode (lihat audit). |
| `docs/design/` | Artefak desain biner (DFD, flowchart, use case, PDF). |
| `docs/security/zap.yaml` | Konfigurasi baseline scan OWASP ZAP. |
| `public/` | Docroot; hanya `index.php`, `.htaccess`, `robots.txt`, `favicon.ico`. |
| `resources/css/app.css` | Entry Tailwind CSS 4. |
| `resources/js/app.js` | JavaScript kecil: toggle password dan konfirmasi form. |
| `resources/views/` | Blade view untuk auth, dashboard, komunitas, acara, admin, ketua, profil, komponen, pagination. |
| `routes/web.php` | Seluruh route web + middleware + throttle. |
| `routes/api.php` | Route API minimal `/api/user`. |
| `routes/console.php` | Command `inspire` bawaan. |
| `storage/` | Direktori runtime Laravel; hanya `.gitignore` yang dilacak. |
| `tests/Feature/` | Feature test HTTP, otorisasi, keamanan, event, membership. |
| `tests/Unit/` | Unit test sanitizer dan contoh. |
| `artisan` | Entry point CLI Laravel. |
| `composer.json` / `composer.lock` | Dependency PHP dan lock versi. |
| `package.json` / `package-lock.json` | Dependency JS dan lock versi. |
| `phpunit.xml` | Konfigurasi test (SQLite in-memory, array cache/session). |
| `vite.config.js` | Konfigurasi Vite, Tailwind, font Bunny. |
| `Dockerfile` | Image PHP-FPM 8.3 + ekstensi + hardening PHP. |
| `docker-compose.yml` | Orkestrasi web/app/db. |
| `Jenkinsfile` | Pipeline CI Windows. |
| `sonar-project.properties` | Konfigurasi SonarCloud. |
| `README.md` | Petunjuk setup, Docker, testing, catatan keamanan. |
| `AGENTS.md`, `CLAUDE.md` | Guidelines pengembangan untuk agen AI. |
| `opencode.json`, `opencode.jsonc`, `.mcp.json`, `boost.json` | Konfigurasi MCP/tooling. |
| `opencode.zip` | Arsip biner 98,9 MB; tidak dipakai runtime. |
| `.env.example` | Template environment. |
| `.gitignore`, `.dockerignore` | Pengecualian file sensitif/artefak. |
| `.editorconfig`, `.gitattributes`, `.npmrc` | Konvensi editor/git/npm. |
| `public/robots.txt` | Menolak seluruh crawling. |

### 2.3 Alur Request dari Browser sampai Response

**A. Request web biasa (contoh: `GET /events/1`)**

1. Browser mengirim request ke `http://127.0.0.1:8000/events/1`.
2. Docker memetakan port ke container `web` (Nginx).
3. Nginx:
   - mengecek metode HTTP (`GET`/`HEAD`/`POST`/`PUT`/`PATCH`/`DELETE`/`OPTIONS`);
   - menolak dotfile, file sensitif, ekstensi berbahaya, dan path direktori internal (`docker/nginx/default.conf` baris 32–46);
   - menerapkan `limit_req` 10 r/s burst 20 (`baris 49`);
   - mencoba file statis; jika tidak ada, mengarahkan ke `public/index.php` (`baris 50`).
4. `public/index.php`:
   - mendefinisikan `LARAVEL_START` (`baris 6`);
   - memuat maintenance file jika ada (`baris 9–11`);
   - memuat Composer autoloader (`baris 14`);
   - membuat application dari `bootstrap/app.php` (`baris 18`);
   - memanggil `handleRequest(Request::capture())` (`baris 20`).
5. `bootstrap/app.php`:
   - mendaftarkan route web/api/console dan health `/up` (`baris 10–15`);
   - menambahkan middleware global `SecurityHeaders` (`baris 17`);
   - mendaftarkan alias `role` → `CheckRole` (`baris 19–21`);
   - mengatur render JSON untuk `api/*` atau request yang meminta JSON (`baris 23–26`).
6. Laravel router mencocokkan route. Untuk `/events/{event}`:
   - middleware `web` (session, CSRF, cookie encryption, dll.);
   - middleware `auth` dari grup (`routes/web.php` baris 58);
   - route model binding mengambil `Event` berdasarkan ID (`whereNumber` baris 77);
   - controller `EventController@show` dipanggil.
7. `EventController@show`:
   - `authorize('view', $event)` (`baris 61`);
   - memuat relasi `creator`, `updater` (`baris 63`);
   - mengambil RSVP dengan pagination `attendance_page` (`baris 65–70`);
   - menghitung status (`baris 72–81`);
   - mengembalikan view + header `Cache-Control: no-store, private` (`baris 83–91`).
8. `SecurityHeaders` menambahkan header ke response (`baris 15–69`), termasuk CSP, HSTS jika HTTPS, dan no-store jika user login.
9. Blade merender `resources/views/events/show.blade.php`; output di-escape oleh `{{ }}`.
10. Nginx mengirim response; untuk response PHP, header aplikasi di-hide lalu header Nginx ditambahkan (`docker/nginx/default.conf` baris 62–71 dan 19–26).

**B. Request API (contoh: `GET /api/user`)**

1. Nginx meneruskan ke `public/index.php`.
2. Laravel memuat route API (`routes/api.php`).
3. Middleware `auth:sanctum` memeriksa session/token.
4. Middleware `throttle:api` membatasi 60 request/menit per user/IP (`AppServiceProvider.php` baris 65–67).
5. Closure mengembalikan JSON `data` berisi `id`, `name`, `email`, `role` saja (`routes/api.php` baris 7–10).
6. `SecurityHeaders` tetap menambahkan header; response API juga mendapat `Cache-Control: no-store, private` jika user terautentikasi.

**C. Request tulis (contoh: `POST /events/{event}/rsvp`)**

1. Nginx → Laravel → route `events.rsvp` (`routes/web.php` baris 80–83).
2. Middleware `web` memvalidasi CSRF token dari form (`@csrf`).
3. `RsvpRequest` memvalidasi `status` hanya `attending`/`declined` (`RsvpRequest.php` baris 16–20).
4. `EventRsvpController@store`:
   - otorisasi `view` (`baris 15`);
   - memastikan profil lengkap (`baris 19–23`);
   - menolak jika acara `finished` (`baris 25–27`);
   - transaksi: `insertOrIgnore` + `lockForUpdate` + `forceFill` (`baris 32–61`);
   - mencatat audit (`baris 55–60`);
   - redirect dengan pesan sukses (`baris 63–66`).

### 2.4 Model Data dan Relasi

**Tabel dan kolom utama (dari migration):**

| Tabel | Kolom penting | Constraint / index |
|---|---|---|
| `users` | `id`, `member_number` (unik, nullable), `name`, `email` (unik), `email_verified_at`, `password`, `remember_token`, `phone`, `profile_photo`, `role` (default `member`), `motorcycle_type`, `license_plate`, timestamps | `users_member_number_unique`; email unik; role default member. |
| `password_reset_tokens` | `email` (primary), `token`, `created_at` | Tidak ada route reset password. |
| `sessions` | `id` (primary), `user_id` (index), `ip_address`, `user_agent`, `payload`, `last_activity` (index) | Session driver database. |
| `cache` | `key` (primary), `value`, `expiration` (index) | Cache database. |
| `cache_locks` | `key` (primary), `owner`, `expiration` (index) | Lock cache. |
| `jobs` | `id`, `queue` (index), `payload`, `attempts`, `reserved_at`, `available_at`, `created_at` | Queue database; tidak ada worker. |
| `job_batches` | `id` (primary), `name`, `total_jobs`, `pending_jobs`, `failed_jobs`, `failed_job_ids`, `options`, `cancelled_at`, `created_at`, `finished_at` | Batching. |
| `failed_jobs` | `id`, `uuid` (unik), `connection`, `queue`, `payload`, `exception`, `failed_at` | Index `(connection, queue, failed_at)`. |
| `communities` | `id`, `name`, `description`, `logo`, `leader_id` (FK users, nullOnDelete), timestamps | Satu komunitas punya satu leader. |
| `community_members` | `id`, `community_id` (FK, cascade), `user_id` (FK, cascade), `status` (string 20, default active), `joined_at`, timestamps | `unique(user_id)` — satu user hanya satu baris membership. |
| `activity_logs` | `id`, `user_id` (FK, nullOnDelete), `activity`, `description`, `ip_address` (45), `user_agent` (500), timestamps | Index `(user_id, created_at)`, `(activity, created_at)`. |
| `personal_access_tokens` | `id`, `tokenable` (morph), `name`, `token` (64, unik), `abilities`, `last_used_at`, `expires_at` (index), timestamps | Sanctum. |
| `member_number_counters` | `id`, `prefix` (20, unik), `last_number` (unsigned bigint, default 0), timestamps | Counter nomor anggota per prefix. |
| `events` | `id`, `title` (150), `description`, `location` (255), `starts_at`, `status` enum, `created_by` (FK, nullOnDelete), `updated_by` (FK, nullOnDelete), timestamps | Index `(status, starts_at)`. |
| `event_rsvps` | `id`, `event_id` (FK, cascade), `user_id` (FK, cascade), `status` enum, `confirmed_at`, timestamps | `unique(event_id, user_id)`; index `(event_id, status)`. |

**Relasi Eloquent:**

- `User hasMany CommunityMember` (`User.php` baris 27–30).
- `User hasMany Community` sebagai leader (`ledCommunities`, baris 32–35).
- `User belongsToMany Community` melalui `community_members` (`communities`, baris 37–46).
- `User hasMany EventRsvp` (`eventRsvps`, baris 48–51).
- `User hasMany Event` sebagai creator (`createdEvents`, baris 53–56).
- `Community belongsTo User` sebagai leader (`Community.php` baris 18–21).
- `Community hasMany CommunityMember` (`members`, baris 23–26).
- `CommunityMember belongsTo User` dan `Community` (`CommunityMember.php` baris 24–32).
- `Event belongsTo User` sebagai creator/updater (`Event.php` baris 60–68).
- `Event hasMany EventRsvp` (`rsvps`, baris 70–73); `attendingRsvps` filter status (`baris 75–78`).
- `EventRsvp belongsTo Event` dan `User` (`EventRsvp.php` baris 47–55).
- `ActivityLog belongsTo User` (`ActivityLog.php` baris 31–34).

**Kardinalitas ringkas:**

```
User 1 ──< CommunityMember >── 1 Community
User 1 ──< Event (created_by)
User 1 ──< EventRsvp >── 1 Event
Community 1 ── 1 User (leader_id)
User 1 ──< ActivityLog
```

### 2.5 Peta Route

**Route web (`routes/web.php`):**

| Method | URI | Name | Middleware | Controller/Action | Akses |
|---|---|---|---|---|---|
| GET | `/register` | `register` | `guest` | `AuthController@showRegister` | Guest |
| POST | `/register` | `register.store` | `guest`, `throttle:registration` | `AuthController@register` | Guest |
| GET | `/login` | `login` | `guest` | `AuthController@showLogin` | Guest |
| POST | `/login` | `login.store` | `guest`, `throttle:login` | `AuthController@login` | Guest |
| POST | `/logout` | `logout` | `auth` | `AuthController@logout` | Semua login |
| GET | `/` | — | — | Closure redirect ke dashboard | Publik |
| GET | `/dashboard` | `dashboard` | `auth` | Closure view dashboard | Semua login |
| GET | `/communities` | `communities.index` | `auth` | `CommunityJoinController@index` | Semua login |
| POST | `/communities/{community}/join` | `communities.join` | `auth`, `throttle:community-join` | `CommunityJoinController@store` | Semua login |
| GET | `/profile` | `profile.edit` | `auth` | `ProfileController@edit` | Semua login |
| PUT | `/profile` | `profile.update` | `auth`, `throttle:profile-update` | `ProfileController@update` | Semua login |
| GET | `/events` | `events.index` | `auth` | `EventController@index` | Semua login |
| GET | `/events/{event}` | `events.show` | `auth`, `whereNumber` | `EventController@show` | Semua login |
| POST | `/events/{event}/rsvp` | `events.rsvp` | `auth`, `whereNumber`, `throttle:rsvp` | `EventRsvpController@store` | Semua login |
| GET | `/events/create` | `events.create` | `auth`, `role:admin,ketua`, `throttle:event-write` | `EventController@create` | Admin/Ketua |
| POST | `/events` | `events.store` | `auth`, `role:admin,ketua`, `throttle:event-write` | `EventController@store` | Admin/Ketua |
| GET | `/events/{event}/edit` | `events.edit` | `auth`, `role:admin,ketua`, `whereNumber`, `throttle:event-write` | `EventController@edit` | Admin/Ketua |
| PUT | `/events/{event}` | `events.update` | `auth`, `role:admin,ketua`, `whereNumber`, `throttle:event-write` | `EventController@update` | Admin/Ketua |
| DELETE | `/events/{event}` | `events.destroy` | `auth`, `role:admin,ketua`, `whereNumber`, `throttle:event-write` | `EventController@destroy` | Admin/Ketua |
| GET | `/events/{event}/attendance/export` | `events.export` | `auth`, `role:admin,ketua`, `whereNumber`, `throttle:event-export` | `EventExportController@download` | Admin/Ketua |
| GET | `/ketua/communities/{community}/requests` | `ketua.communities.requests.index` | `auth`, `role:ketua` | `CommunityRequestController@index` | Ketua pemilik |
| PATCH | `/ketua/communities/{community}/requests/{membership}/approve` | `ketua.communities.requests.approve` | `auth`, `role:ketua`, `throttle:leader-actions` | `CommunityRequestController@approve` | Ketua pemilik |
| PATCH | `/ketua/communities/{community}/requests/{membership}/reject` | `ketua.communities.requests.reject` | `auth`, `role:ketua`, `throttle:leader-actions` | `CommunityRequestController@reject` | Ketua pemilik |
| GET | `/admin/dashboard` | `admin.dashboard` | `auth`, `role:admin` | `AdminController@dashboard` | Admin |
| GET | `/admin/users` | `admin.users.index` | `auth`, `role:admin` | `Admin\UserController@index` | Admin |
| PATCH | `/admin/users/{user}/role` | `admin.users.role.update` | `auth`, `role:admin`, `whereNumber`, `throttle:member-role` | `Admin\UserController@updateRole` | Admin |
| GET | `/admin/communities` | `admin.communities.index` | `auth`, `role:admin` | `CommunityController@index` | Admin |
| GET | `/admin/communities/select` | `admin.communities.select` | `auth`, `role:admin` | `CommunityController@select` | Admin |
| GET | `/admin/communities/create` | `admin.communities.create` | `auth`, `role:admin` | `CommunityController@create` | Admin |
| POST | `/admin/leader-accounts` | `admin.leader-accounts.store` | `auth`, `role:admin`, `throttle:admin-actions` | `AdminController@storeLeaderAccount` | Admin |
| POST | `/admin/communities` | `admin.communities.store` | `auth`, `role:admin`, `throttle:admin-actions` | `CommunityController@store` | Admin |
| GET | `/admin/communities/{community}/members` | `admin.communities.members.index` | `auth`, `role:admin` | `MemberController@index` | Admin |
| GET | `/admin/communities/{community}/members/create` | `admin.communities.members.create` | `auth`, `role:admin` | `MemberController@create` | Admin |
| POST | `/admin/communities/{community}/members` | `admin.communities.members.store` | `auth`, `role:admin`, `throttle:admin-actions` | `MemberController@store` | Admin |
| DELETE | `/admin/communities/{community}/members/{membership}` | `admin.communities.members.destroy` | `auth`, `role:admin`, `throttle:admin-actions` | `MemberController@destroy` | Admin |
| PUT | `/admin/communities/{community}/leader` | `admin.communities.leader.update` | `auth`, `role:admin`, `throttle:admin-actions` | `CommunityController@updateLeader` | Admin |

**Route API (`routes/api.php`):**

| Method | URI | Middleware | Handler | Akses |
|---|---|---|---|---|
| GET | `/api/user` | `auth:sanctum`, `throttle:api` | Closure JSON `id,name,email,role` | User terautentikasi |

**Route health (`bootstrap/app.php`):**

| Method | URI | Handler |
|---|---|---|
| GET | `/up` | Health check Laravel |

> Catatan: route `/` tidak memakai middleware `auth`; route ini hanya redirect ke `/dashboard`, yang kemudian dilindungi `auth`. Route `/events/create` didefinisikan sebelum `/events/{event}` agar tidak tertangkap sebagai ID; `whereNumber` juga melindungi.

### 2.6 Matriks Otorisasi

| Aksi | Guest | Member | Ketua | Admin |
|---|---|---|---|---|
| Registrasi/login | Ya | Tidak (guest middleware) | Tidak | Tidak |
| Lihat dashboard | Tidak | Ya | Ya | Ya |
| Lihat daftar komunitas | Tidak | Ya | Ya | Ya |
| Ajukan bergabung | Tidak | Ya | Ya (jika belum punya membership) | Ya (jika belum punya membership) |
| Setujui/tolak permintaan | Tidak | Tidak | Hanya komunitas sendiri | Tidak (route hanya ketua) |
| Lihat profil sendiri | Tidak | Ya | Ya | Ya |
| Ubah profil sendiri | Tidak | Ya | Ya | Ya |
| Lihat acara | Tidak | Ya | Ya | Ya |
| Buat/ubah/hapus acara | Tidak | Tidak | Ya (semua acara, bukan hanya komunitas sendiri) | Ya |
| RSVP | Tidak | Ya (profil lengkap) | Ya (profil lengkap) | Ya (profil lengkap) |
| Ekspor kehadiran | Tidak | Tidak | Ya (semua acara) | Ya |
| Panel admin | Tidak | Tidak | Tidak | Ya |
| Ubah role pengguna | Tidak | Tidak | Tidak | Ya (bukan akun sendiri) |
| Buat komunitas/tunjuk ketua | Tidak | Tidak | Tidak | Ya |
| Tambah/keluarkan anggota | Tidak | Tidak | Tidak | Ya |
| Ganti ketua komunitas | Tidak | Tidak | Tidak | Ya |

### 2.7 Alur Bisnis Utama

**A. Registrasi publik**

1. `GET /register` → form (`auth/register.blade.php`).
2. `POST /register` → `AuthController@register`.
3. Normalisasi input: email lowercase/trim, name trim, motorcycle trim, license plate uppercase (`AuthController.php` baris 25–30).
4. Validasi: name ≤100, email unik ≤255, phone nullable ≤20, motorcycle ≤100, license plate regex `^[A-Z0-9][A-Z0-9 .-]*$`, password min 8 + mixed case + number + symbol + confirmed (`baris 32–43`).
5. Transaksi: `User::create` tanpa `role`/`member_number`; `MemberNumberService::assignTo` (`baris 45–56`).
6. `Auth::login`, `session()->regenerate` (`baris 58–59`).
7. Audit `auth.register` (`baris 61`).
8. Redirect berdasarkan role/membership (`redirectByRole`, baris 105–130).

**B. Login**

1. `GET /login` → form.
2. `POST /login` → normalisasi email, validasi kredensial (`AuthController.php` baris 73–80).
3. `Auth::attempt($credentials, remember)` (`baris 82`).
4. Gagal → audit `auth.login.failed`, error generik “Email atau password tidak sesuai”, hanya input email (`baris 83–92`).
5. Berhasil → `session()->regenerate`, audit `auth.login.success`, redirect by role (`baris 95–102`).
6. Rate limit: 5/menit per email+IP dan 30/menit per IP (`AppServiceProvider.php` baris 20–27).

**C. Pengajuan dan persetujuan bergabung**

1. Member membuka `/communities` → `CommunityJoinController@index` mengambil komunitas + membership user (`baris 13–25`).
2. `POST /communities/{community}/join` → cek membership aktif/pending (`baris 32–39`).
3. Transaksi `updateOrCreate` membership status `pending`, `joined_at=null` (`baris 41–51`); audit (`baris 53–57`).
4. Ketua membuka `/ketua/communities/{community}/requests` → cek `leader_id === auth()->id()` (`CommunityRequestController.php` baris 15), ambil pending (`baris 17–21`).
5. Approve: cek otorisasi (`baris 31`), transaksi lock membership (`baris 34–36`), pastikan status pending (`baris 38`), pastikan belum aktif di komunitas lain (`baris 40–47`), update active + `joined_at`, assign nomor anggota jika belum ada, audit (`baris 49–59`).
6. Reject: lock, pastikan pending, update `rejected`, audit (`baris 69–82`).

**D. Admin membuat komunitas dan menunjuk Ketua**

1. `GET /admin/communities/create` → daftar user role `member` yang belum punya membership active/pending (`CommunityController.php` baris 40–46).
2. `POST /admin/communities` → validasi name/description/logo/leader_id (`baris 54–62`); cek leader belum punya membership (`baris 64–72`).
3. Transaksi: buat `Community`, promosikan `User.role=ketua` server-side (`baris 75–83`), `CommunityMember::updateOrCreate` active (`baris 85–92`), audit (`baris 94–97`).
4. Jika belum ada calon ketua, Admin dapat membuat akun baru via `POST /admin/leader-accounts` (`AdminController@storeLeaderAccount`), password min 12, role default member, nomor anggota dibuat (`baris 22–60`).

**E. Acara dan RSVP**

1. Admin/Ketua `GET /events/create` → `EventController@create` authorize `create` (`baris 32–36`).
2. `POST /events` → `EventRequest` validasi title/description/location/starts_at/status (`EventRequest.php` baris 24–32); `EventController@store` set `created_by`/`updated_by` dari user, simpan, audit (`baris 39–56`).
3. Semua user login `GET /events` → daftar acara + hitung RSVP (`EventController.php` baris 14–29).
4. `GET /events/{event}` → detail + daftar kehadiran + status sendiri (`baris 59–91`).
5. `POST /events/{event}/rsvp` → validasi status, cek profil lengkap, cek acara belum finished, transaksi insert/update RSVP, audit (`EventRsvpController.php` baris 13–66).
6. Admin/Ketua `GET /events/{event}/attendance/export` → `EventExportController@download` authorize `export`, buat XLSX, audit, response attachment (`baris 12–33`).

**F. Perubahan role oleh Admin**

1. `GET /admin/users` → daftar user + membership (`Admin\UserController.php` baris 14–35).
2. `PATCH /admin/users/{user}/role` → `UpdateMemberRoleRequest` authorize admin + validasi role (`UpdateMemberRoleRequest.php` baris 11–19).
3. Controller menolak perubahan role akun sendiri (`Admin\UserController.php` baris 41–43).
4. Transaksi lock user, jika role sama return, update role, audit (`baris 47–64`).

**G. Logout**

1. `POST /logout` → `AuthController@logout`.
2. Audit `auth.logout` sebelum logout (`baris 134–135`).
3. `Auth::logout`, `session()->invalidate`, `session()->regenerateToken` (`baris 137–139`).
4. Redirect ke login.

### 2.8 Pola Arsitektur dan Konvensi

- **Thin controller, service untuk domain kompleks:** controller menangani HTTP; `MemberNumberService`, `AttendanceExcelExporter`, `XlsxWriter`, `ActivityLogger`, `SpreadsheetValueSanitizer` menangani logika khusus.
- **FormRequest untuk validasi/otorisasi input:** `EventRequest`, `RsvpRequest`, `UpdateMemberRoleRequest`, `UpdateProfileRequest`.
- **Policy untuk otorisasi model:** `EventPolicy`.
- **Middleware untuk RBAC dan header:** `CheckRole`, `SecurityHeaders`.
- **Transaksi database untuk operasi multi-tabel:** pembuatan komunitas, approval membership, perubahan leader, RSVP, perubahan role, assign nomor anggota.
- **Locking untuk race condition:** `lockForUpdate()` pada counter nomor anggota, membership approval, user role, RSVP.
- **Audit trail terpusat:** `ActivityLogger::record` dipanggil di alur auth, community, event, profile, role, export.
- **Blade components:** `x-alert`, `x-empty-state`, `x-page-header`, `x-password-field`.
- **Server-side trust boundary:** field `role`, `member_number`, `created_by`, `updated_by`, `user_id`, `event_id` tidak diambil dari input client; diisi server.
- **Response caching:** halaman terautentikasi dan export diberi `no-store, private`.
- **Tanpa upload file:** tidak ada `Storage::put` atau validasi `file`; mengurangi risiko upload malware/path traversal.

---

## 3. ANALISIS STRIDE

### 3.1 Matriks Ringkasan

Legenda status:

- **Terimplementasi** — kontrol ditemukan di kode/konfigurasi dan (bila relevan) diuji.
- **Belum Lengkap** — kontrol ada, tetapi cakupannya terbatas, memiliki race condition, atau hanya berlaku pada lapisan tertentu.
- **Belum Ada Mitigasi** — tidak ditemukan kontrol yang memadai di repository.

| Kategori | Komponen | Ancaman | Mitigasi di Kode | File & Nomor Baris | Status |
|---|---|---|---|---|---|
| **S** | Login web | Penebakan kredensial / credential stuffing | `Auth::attempt`, pesan error generik, rate limit 5/menit per email+IP dan 30/menit per IP | `app/Http/Controllers/AuthController.php` 71–103; `app/Providers/AppServiceProvider.php` 20–27; `routes/web.php` 28–30 | Terimplementasi |
| **S** | Session | Session fixation | `session()->regenerate()` setelah login/register; `invalidate()` + `regenerateToken()` saat logout | `AuthController.php` 58–59, 95, 137–139 | Terimplementasi |
| **S** | Cookie session | Pencurian cookie / pengiriman via HTTP | `SESSION_ENCRYPT=true`, `http_only=true`, `secure` otomatis di production, `same_site=lax`, serialisasi JSON | `config/session.php` 21, 50, 172, 185, 202, 231; `.env.example` 34–43 | Terimplementasi |
| **S** | Registrasi | Pembuatan akun massal | Rate limit 3/jam per IP | `AppServiceProvider.php` 29–31; `routes/web.php` 21–23 | Terimplementasi |
| **S** | Password | Password lemah | `Password::min(8)->mixedCase()->numbers()->symbols()` untuk member; min 12 untuk akun ketua buatan admin | `AuthController.php` 38–42; `AdminController.php` 32–36; `tests/Feature/PasswordPolicyTest.php` 12–47 | Terimplementasi |
| **S** | API | Akses API tanpa identitas | `auth:sanctum` + `throttle:api` | `routes/api.php` 7–11; `config/sanctum.php` 40 | Terimplementasi |
| **S** | API token | Token tidak kedaluwarsa | `expiration => null`; tidak ada route pembuatan token | `config/sanctum.php` 53 | Belum Lengkap |
| **S** | Autentikasi | MFA tidak tersedia | Tidak ada TOTP/WebAuthn/email OTP | — | Belum Ada Mitigasi |
| **S** | Reset password | Tidak ada alur reset | Tabel `password_reset_tokens` ada, tetapi tidak ada route/controller | `database/migrations/0001_01_01_000000_create_users_table.php` 24–28; `config/auth.php` 95–101 | Belum Ada Mitigasi |
| **S** | Verifikasi email | Email tidak diverifikasi | Kolom `email_verified_at` ada, tetapi tidak ada middleware/route verifikasi | `0001_01_01_000000_create_users_table.php` 18 | Belum Lengkap |
| **T** | Semua input form | Mass assignment / field injection | FormRequest dan `$request->validate`; `role`, `member_number`, `created_by`, `updated_by`, `user_id`, `event_id` diabaikan dari input | `EventRequest.php` 24–32; `RsvpRequest.php` 16–20; `UpdateMemberRoleRequest.php` 15–19; `UpdateProfileRequest.php` 24–32 | Terimplementasi |
| **T** | Model `User` | Eskalasi role via mass assignment | `#[Fillable]` hanya field profil; `role`/`member_number` diisi `forceFill` server-side | `app/Models/User.php` 12–20; `app/Support/MemberNumberService.php` 57–61 | Terimplementasi |
| **T** | Nomor anggota | Duplikasi nomor karena race condition | Transaksi + `lockForUpdate()` pada `member_number_counters`; unique index | `MemberNumberService.php` 25–64; `2026_11_18_000001_create_member_number_counters_table.php` 11–16; `2026_11_18_000002_add_member_profile_fields_to_users_table.php` 49–51 | Terimplementasi |
| **T** | Membership | Dua anggota/pengajuan untuk satu user | Unique `community_members.user_id`; `updateOrCreate` untuk reuse baris rejected | `2026_10_01_180507_create_community_members_table.php` 32; `CommunityJoinController.php` 44–51 | Terimplementasi |
| **T** | Approval membership | Approve ganda / balapan | Transaksi + `lockForUpdate`; cek status `pending`; cek keanggotaan aktif lain | `CommunityRequestController.php` 33–60 | Terimplementasi |
| **T** | RSVP | RSVP ganda / ubah RSVP acara selesai | Unique `(event_id,user_id)`; `insertOrIgnore`; `lockForUpdate`; cek `isFinished()`; retry 3x | `EventRsvpController.php` 25–61; `2026_11_18_000004_create_event_rsvps_table.php` 27 | Terimplementasi |
| **T** | Query database | SQL injection | Eloquent/query builder dengan binding; `orderByRaw` hanya memakai ekspresi statis | `Admin/UserController.php` 20–29; `2026_11_18_000002_add_member_profile_fields_to_users_table.php` 24 | Terimplementasi |
| **T** | Output Blade | XSS | Semua output memakai `{{ }}`; tidak ada `{!! !!}`; CSP `script-src 'self'` | `resources/views/**`; `SecurityHeaders.php` 41–59 | Terimplementasi |
| **T** | Export Excel | Formula/CSV injection | Sanitasi nilai berawalan `= + - @ TAB CR LF`; escape XML; nama file disanitasi | `SpreadsheetValueSanitizer.php` 7–17; `AttendanceExcelExporter.php` 34–49, 52–67; `XlsxWriter.php` 70–72 | Terimplementasi |
| **T** | Form web | CSRF | Middleware `web` Laravel + `@csrf` pada seluruh form; tidak ada pengecualian CSRF | `routes/web.php`; `resources/views/**`; `bootstrap/app.php` 16–22 | Terimplementasi |
| **T** | IDOR membership | Ketua mengelola membership komunitas lain | Cek `leader_id === auth()->id()` dan `membership->community_id === community->id` | `CommunityRequestController.php` 87–92; `MemberController.php` 95–98 | Terimplementasi |
| **T** | Integritas relasi | Foreign key dimatikan / data yatim | Semua relasi memakai `constrained()`; `cascadeOnDelete`/`nullOnDelete` eksplisit | `database/migrations/*` | Terimplementasi |
| **T** | Event | Ketua mengubah/menghapus acara komunitas lain | Policy hanya memeriksa role `admin`/`ketua`, bukan kepemilikan komunitas | `EventPolicy.php` 20–38 | Belum Lengkap |
| **R** | Audit trail | Aktivitas tidak tercatat | `ActivityLogger::record` dipanggil pada auth, komunitas, event, RSVP, role, profil, export | `ActivityLogger.php` 14–26; `AuthController.php` 61, 83–86, 96–100, 134–135 | Terimplementasi |
| **R** | Audit context | Log tanpa IP/user-agent | Kolom `ip_address` 45 dan `user_agent` 500; index `(user_id,created_at)` dan `(activity,created_at)` | `2026_11_16_200002_add_audit_request_context.php` 11–16; `ActivityLogger.php` 23–24 | Terimplementasi |
| **R** | Immutability log | Log diubah/dihapus lewat model | Event `updating`/`deleting` mengembalikan `false` | `ActivityLog.php` 20–29; `tests/Feature/StrideSecurityTest.php` 178–192 | Terimplementasi |
| **R** | Audit UI | Admin tidak dapat melihat log | Tidak ada route/view audit log | — | Belum Lengkap |
| **R** | Immutability DB | User database dapat mengubah log via SQL | Tidak ada trigger/permission khusus | — | Belum Ada Mitigasi |
| **R** | Retensi log | Log hilang/tidak dibatasi | Tidak ada kebijakan retensi/arsip | — | Belum Ada Mitigasi |
| **I** | Response header | Clickjacking, MIME sniffing, referrer leak | `X-Frame-Options DENY`, `nosniff`, `Referrer-Policy`, `Permissions-Policy`, COOP/CORP | `SecurityHeaders.php` 15–29; `docker/nginx/default.conf` 19–26; `public/.htaccess` 1–10 | Terimplementasi |
| **I** | CSP | Eksekusi script inline/XSS | CSP enforcing tanpa `'unsafe-inline'`; hanya `'self'` (+ Vite localhost saat `hot`) | `SecurityHeaders.php` 31–59; `tests/Feature/SecurityHeadersTest.php` 14–37 | Terimplementasi |
| **I** | HSTS | Downgrade HTTPS | HSTS `max-age=31536000; includeSubDomains` jika request secure | `SecurityHeaders.php` 61–63 | Terimplementasi |
| **I** | Cache response | Data sensitif tersimpan di cache browser/proxy | `Cache-Control: no-store, private` untuk user login, detail acara, dan export | `SecurityHeaders.php` 65–67; `EventController.php` 91; `EventExportController.php` 30 | Terimplementasi |
| **I** | Path traversal / file exposure | `.env`, `.git`, log, database, source terbaca | Nginx deny dotfile, file sensitif, ekstensi berbahaya, direktori internal; Apache deny dotfile; docroot `public/` | `docker/nginx/default.conf` 32–46; `public/.htaccess` 12–14 | Terimplementasi |
| **I** | API | Kebocoran atribut profil | Response hanya `id`, `name`, `email`, `role` | `routes/api.php` 6–10 | Terimplementasi |
| **I** | Model | Password/token tampil di serialisasi | `#[Hidden(['password','remember_token'])]`; cast `hashed` | `User.php` 21, 80–86 | Terimplementasi |
| **I** | Konfigurasi | Debug/stack trace bocor | `APP_DEBUG=false`; `display_errors=Off`; `expose_php=Off` | `.env.example` 4; `config/app.php` 42; `Dockerfile` 21–32 | Terimplementasi |
| **I** | Secret repository | Kredensial ikut Git/Docker | `.env*` diabaikan; `.dockerignore` mengecualikan `.env`, `.git`, storage, database | `.gitignore` 3–7; `.dockerignore` 5–7, 18–29 | Terimplementasi |
| **I** | Session storage | Payload session terbaca di DB | `SESSION_ENCRYPT=true`; serialisasi `json` | `config/session.php` 50, 231 | Terimplementasi |
| **I** | Nama file export | Header injection | Slug `[A-Za-z0-9]`, maksimal 80 karakter, fallback `Acara` | `AttendanceExcelExporter.php` 52–67 | Terimplementasi |
| **I** | Artefak biner | `opencode.zip` 98,9 MB ter-track Git | Tidak ada pemeriksaan isi/secret scan | `opencode.zip` | Belum Lengkap |
| **I** | TLS/proxy | Aplikasi tidak tahu request HTTPS di balik proxy | Tidak ada `TrustProxies`; Compose hanya listen port 80 | `docker-compose.yml` 5–6; `docker/nginx/default.conf` 3–5 | Belum Ada Mitigasi |
| **I** | Log PII | IP/user-agent tersimpan tanpa retensi | Kolom audit menyimpan PII | `ActivityLogger.php` 23–24 | Belum Lengkap |
| **D** | Rate limiting aplikasi | Flooding endpoint | Named limiter: login, registration, join, rsvp, profile, event-write, export, role, admin, leader, api | `AppServiceProvider.php` 20–67 | Terimplementasi |
| **D** | Rate limiting proxy | Flooding HTTP | `limit_req` 10 r/s burst 20; status 429 | `docker/nginx/default.conf` 1, 17, 49, 54 | Terimplementasi |
| **D** | Body/timeout | Slowloris / payload besar | `client_max_body_size 2m`; timeout 10–15 detik | `docker/nginx/default.conf` 11–15 | Terimplementasi |
| **D** | Pagination | Query/response besar | Pagination 10–25 baris; `limit(500)` pada kandidat | `EventController.php` 21, 69; `CommunityController.php` 21, 32, 45; `MemberController.php` 23, 39; `Admin/UserController.php` 31 | Terimplementasi |
| **D** | Pencarian | Query mahal | `mb_substr(...,0,100)`; `LIKE` dengan bind | `Admin/UserController.php` 16–29 | Terimplementasi |
| **D** | Export | Ekspor berulang | 5/menit per user; `no-store` | `AppServiceProvider.php` 49–51; `routes/web.php` 110–113; `EventMembershipTest.php` 241–255 | Terimplementasi |
| **D** | Registrasi/login | Bot/CAPTCHA | Tidak ada CAPTCHA | — | Belum Ada Mitigasi |
| **D** | Rate limit store | Limit bergantung database | `CACHE_STORE=database`; tidak ada Redis | `.env.example` 48; `config/cache.php` 18 | Belum Lengkap |
| **D** | Queue | Job menumpuk tanpa worker | `QUEUE_CONNECTION=database`; Compose tidak punya worker | `.env.example` 47; `docker-compose.yml` 1–52 | Belum Lengkap |
| **D** | Export besar | Satu acara dengan RSVP sangat banyak | Tidak ada batas jumlah baris/streaming | `AttendanceExcelExporter.php` 25–49 | Belum Lengkap |
| **D** | DDoS volumetrik | Serangan bandwidth/infra | Tidak ada CDN/WAF; hanya Nginx | `docs/SECURITY_STRIDE.md` 84–90 | Belum Ada Mitigasi |
| **E** | RBAC | Member mengakses admin/ketua | Middleware `role` pada grup route | `CheckRole.php` 11–25; `routes/web.php` 86, 116, 135 | Terimplementasi |
| **E** | Otorisasi event | Member mengelola acara | `EventPolicy` + `authorize()` di controller | `EventPolicy.php` 20–38; `EventController.php` 34, 41, 61, 96, 103, 122 | Terimplementasi |
| **E** | Kepemilikan komunitas | Ketua lain mengelola permintaan | Cek `leader_id` dan `community_id` | `CommunityRequestController.php` 15, 87–92 | Terimplementasi |
| **E** | Role | Member menaikkan role sendiri | `role` tidak fillable; registrasi/profil mengabaikan `role` | `User.php` 12–20; `AuthController.php` 45–56; `ProfileController.php` 20–22 | Terimplementasi |
| **E** | Admin | Admin mengubah role sendiri / lockout | Ditolak jika `user->id === actor->id` | `Admin/UserController.php` 41–43; `EventMembershipTest.php` 257–279 | Terimplementasi |
| **E** | Akun ketua baru | Role dari input client | `AdminController` tidak memvalidasi/memakai `role`; default model `member` | `AdminController.php` 28–55; `2026_10_01_180635_add_fields_to_users_table.php` 17 | Terimplementasi |
| **E** | Nomor anggota | Nomor 1/2 otomatis jadi admin/ketua | Mapping hanya dipakai `assignTo(..., true)` pada seeder/admin tepercaya; registrasi publik `false` | `MemberNumberService.php` 19–64, 67–74; `AuthController.php` 55; `AdminUserSeeder.php` 31 | Terimplementasi |
| **E** | Event horizontal | Ketua mengelola acara komunitas lain | Policy tidak memeriksa `community_id` acara (acara tidak memiliki `community_id`) | `EventPolicy.php` 20–38 | Belum Lengkap |
| **E** | Admin terakhir | Semua admin dapat diturunkan | Tidak ada perlindungan “minimal satu admin” | `Admin/UserController.php` 37–67 | Belum Lengkap |
| **E** | MFA admin | Takeover akun admin | Tidak ada MFA | — | Belum Ada Mitigasi |
| **E** | Mass assignment membership | `status`/`community_id` dapat diisi jika ada kode baru | `CommunityMember` memakai `$fillable` luas | `CommunityMember.php` 10–15 | Belum Lengkap |
| **E** | Token API | Token tanpa ability/expiry | Tidak ada pembuatan token; expiry `null` | `config/sanctum.php` 53 | Belum Lengkap |

### 3.2 Audit Rinci per Kategori

#### 3.2.1 Spoofing Identity (S)

**S-1. Autentikasi kredensial dan pesan error generik.**

`AuthController@login` menormalisasi email, memvalidasi tipe input, lalu memanggil `Auth::attempt`. Jika gagal, aplikasi tidak membedakan “email tidak ada” dan “password salah”; keduanya menghasilkan pesan yang sama. Ini mengurangi user enumeration lewat pesan login.

```php
// app/Http/Controllers/AuthController.php 77–92
$credentials = $request->validate([
    'email' => ['required', 'email', 'max:255'],
    'password' => ['required', 'string', 'max:255'],
]);

if (! Auth::attempt($credentials, $request->boolean('remember'))) {
    ActivityLogger::record('auth.login.failed', 'Percobaan autentikasi gagal.');

    return back()
        ->withErrors(['email' => 'Email atau password tidak sesuai.'])
        ->onlyInput('email');
}
```

Keterbatasan: registrasi tetap dapat mengungkap keberadaan email melalui aturan `unique:users,email`, dan tidak ada verifikasi email. Ini bukan eskalasi hak akses, tetapi membantu enumerasi akun.

**S-2. Session fixation dan logout.**

Session ID diregenerasi setelah registrasi dan login, serta diinvalidasi saat logout. Token CSRF juga diregenerasi.

```php
// AuthController.php 58–59
Auth::login($user);
$request->session()->regenerate();

// AuthController.php 137–139
Auth::logout();
$request->session()->invalidate();
$request->session()->regenerateToken();
```

**S-3. Cookie session.**

`config/session.php` memakai driver database, enkripsi payload aktif, cookie `HttpOnly`, `SameSite=Lax`, dan `Secure` otomatis ketika `APP_ENV=production`. `.env.example` tidak lagi memaksa `SESSION_SECURE_COOKIE=false`.

```php
// config/session.php 172, 185, 202
'secure' => env('SESSION_SECURE_COOKIE', env('APP_ENV') === 'production'),
'http_only' => env('SESSION_HTTP_ONLY', true),
'same_site' => env('SESSION_SAME_SITE', 'lax'),
```

Keterbatasan: jika aplikasi berada di balik reverse proxy TLS yang tidak dipercaya (`TrustProxies` tidak dikonfigurasi), Laravel dapat melihat request sebagai HTTP sehingga `secure` dan HSTS tidak aktif. Ini dibahas di I-12.

**S-4. Rate limit login dan registrasi.**

Login dibatasi dua lapis: 5 percobaan/menit per kombinasi email+IP dan 30 percobaan/menit per IP. Registrasi dibatasi 3/jam per IP.

```php
// app/Providers/AppServiceProvider.php 20–31
RateLimiter::for('login', function (Request $request) {
    $email = Str::lower(trim((string) $request->input('email')));

    return [
        Limit::perMinute(5)->by($email.'|'.$request->ip()),
        Limit::perMinute(30)->by('login-ip:'.$request->ip()),
    ];
});

RateLimiter::for('registration', function (Request $request) {
    return Limit::perHour(3)->by($request->ip());
});
```

Keterbatasan: limiter memakai `CACHE_STORE=database`. Jika database lambat/down, proteksi ikut terganggu. Distribusi IP (botnet) juga dapat melewati batas per IP. Tidak ada CAPTCHA atau account lockout.

**S-5. Password policy.**

Registrasi publik memakai minimal 8 karakter dengan huruf besar, huruf kecil, angka, dan simbol. Akun ketua yang dibuat Admin memakai minimal 12 karakter.

```php
// AuthController.php 38–42
'password' => [
    'required',
    'confirmed',
    Password::min(8)->mixedCase()->numbers()->symbols(),
],

// AdminController.php 32–36
'leader_password' => [
    'required',
    'confirmed',
    Password::min(12)->mixedCase()->numbers()->symbols(),
],
```

**S-6. API authentication.**

Satu-satunya route API adalah `/api/user`, dilindungi `auth:sanctum` dan `throttle:api`. Response dibatasi pada empat field.

```php
// routes/api.php 6–10
Route::get('/user', function (Request $request) {
    return response()->json([
        'data' => $request->user()->only(['id', 'name', 'email', 'role']),
    ]);
})->middleware(['auth:sanctum', 'throttle:api']);
```

**S-7. Celah Spoofing yang belum termitigasi.**

- **MFA tidak ada.** Tidak ditemukan TOTP, WebAuthn, atau OTP. Akun admin hanya dilindungi password.
- **Reset password tidak ada.** Tabel `password_reset_tokens` dibuat, konfigurasi broker ada, tetapi tidak ada route `password.request`/`password.reset`. Pengguna yang lupa password tidak punya alur pemulihan yang aman.
- **Verifikasi email tidak diberlakukan.** Kolom `email_verified_at` ada dan factory mengisinya, tetapi registrasi tidak mengirim verifikasi dan tidak ada middleware `verified`.
- **Token Sanctum tidak kedaluwarsa.** `config/sanctum.php` baris 53 menetapkan `expiration => null`. Route pembuatan token juga tidak ada, sehingga risiko saat ini rendah, tetapi konfigurasi ini tidak aman bila token mulai diterbitkan.

#### 3.2.2 Tampering with Data (T)

**T-1. Validasi input terpusat.**

Semua input tulis divalidasi melalui FormRequest atau `$request->validate`. Contoh `EventRequest` membatasi panjang dan nilai enum; `RsvpRequest` hanya menerima dua status; `UpdateProfileRequest` menormalisasi plat nomor dan memvalidasi regex.

```php
// app/Http/Requests/EventRequest.php 24–32
return [
    'title' => ['required', 'string', 'max:150'],
    'description' => ['required', 'string', 'max:5000'],
    'location' => ['required', 'string', 'max:255'],
    'starts_at' => ['required', 'date'],
    'status' => ['required', Rule::in(Event::statuses())],
];

// app/Http/Requests/RsvpRequest.php 16–20
return [
    'status' => ['required', Rule::in(EventRsvp::statuses())],
];
```

**T-2. Mass assignment role dan nomor anggota.**

`User` memakai atribut `#[Fillable]` yang tidak memuat `role` maupun `member_number`. Registrasi hanya mengirim field profil; nomor anggota dan role diisi server-side.

```php
// app/Models/User.php 12–20
#[Fillable([
    'name',
    'email',
    'phone',
    'motorcycle_type',
    'license_plate',
    'profile_photo',
    'password',
])]
```

```php
// app/Support/MemberNumberService.php 53–61
$attributes = [
    'member_number' => sprintf('%s-%02d', $prefix, $sequence),
];

if ($applySequenceRole) {
    $attributes['role'] = $this->roleForSequence($sequence);
}

$user->forceFill($attributes)->save();
```

Pengujian `test_public_registration_ignores_privileged_member_fields` dan `test_profile_update_cannot_change_role_or_member_number` membuktikan input `role`/`member_number` diabaikan.

**T-3. Transaksi dan locking.**

Operasi multi-tabel memakai `DB::transaction`. Titik rawan memakai `lockForUpdate()`:

- `MemberNumberService` mengunci baris counter sebelum menaikkan nomor (baris 35–38).
- `CommunityRequestController@approve` mengunci membership sebelum mengubah status (baris 34–36).
- `EventRsvpController@store` memakai `insertOrIgnore` lalu mengunci baris RSVP (baris 33–46).
- `Admin\UserController@updateRole` mengunci user sebelum mengubah role (baris 48).

```php
// app/Support/MemberNumberService.php 35–44
$counter = DB::table('member_number_counters')
    ->where('prefix', $prefix)
    ->lockForUpdate()
    ->first();

if (! $counter) {
    throw new RuntimeException('Member number counter could not be locked.');
}

$sequence = ((int) $counter->last_number) + 1;
```

**T-4. Constraint database.**

Unique constraint mencegah duplikasi: email, `member_number`, `community_members.user_id`, dan `(event_id,user_id)` pada RSVP. Foreign key memakai `constrained()` dengan `cascadeOnDelete` atau `nullOnDelete`, sehingga tidak ada bypass `FOREIGN_KEY_CHECKS=0` seperti pada versi GPS lama.

**T-5. SQL injection.**

Tidak ditemukan query yang menyisipkan input user langsung ke SQL. Pencarian admin memakai binding:

```php
// app/Http/Controllers/Admin/UserController.php 20–29
->when($search !== '', function ($query) use ($search) {
    $like = '%'.$search.'%';

    $query->where(function ($query) use ($like) {
        $query->where('name', 'like', $like)
            ->orWhere('email', 'like', $like)
            ->orWhere('member_number', 'like', $like)
            ->orWhere('license_plate', 'like', $like);
    });
})
```

Satu-satunya `orderByRaw` berada di migration dan memakai string statis `CASE WHEN role = ...`, bukan input user.

**T-6. XSS.**

Tidak ditemukan `{!! !!}` di seluruh Blade. Semua output dinamis memakai `{{ }}` yang di-escape. CSP juga melarang script inline dan hanya mengizinkan `'self'` (ditambah Vite localhost saat file `public/hot` ada).

**T-7. Formula injection pada Excel.**

`SpreadsheetValueSanitizer` menghapus karakter kontrol dan memberi prefix apostrof pada nilai yang dimulai `=`, `+`, `-`, `@`, tab, CR, atau LF. `XlsxWriter` meng-escape XML dengan `htmlspecialchars(..., ENT_XML1)`.

```php
// app/Support/SpreadsheetValueSanitizer.php 7–17
$value = (string) $value;
$value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $value) ?? '';

if (preg_match('/^[=+\-@\t\r\n]/', $value) === 1) {
    $value = "'".$value;
}

return mb_substr($value, 0, 32767);
```

**T-8. CSRF.**

Semua form web memakai `@csrf`, dan tidak ada route web yang dikecualikan dari middleware CSRF. Untuk request API stateful, Sanctum memakai `ValidateCsrfToken` (config/sanctum.php baris 84). Ini adalah kontrol framework default; tidak ada konfigurasi tambahan di repository.

**T-9. IDOR dan kepemilikan sumber daya.**

`CommunityRequestController` memverifikasi tiga hal sebelum approve/reject: user adalah leader komunitas, membership berasal dari komunitas tersebut, dan status masih `pending`. `MemberController@destroy` memverifikasi `membership->community_id === community->id`.

```php
// app/Http/Controllers/CommunityRequestController.php 87–92
private function authorizeMembershipAction(Community $community, CommunityMember $membership): void
{
    abort_unless((int) $community->leader_id === (int) auth()->id(), 403);
    abort_unless((int) $membership->community_id === (int) $community->id, 404);
    abort_unless($membership->status === 'pending', 404);
}
```

**T-10. Celah Tampering yang belum lengkap.**

- **EventPolicy tidak membatasi kepemilikan.** Setiap `ketua` dapat mengubah/menghapus/mengekspor acara apa pun, karena policy hanya memanggil `canManageEvents()` (role-based). Tidak ada `community_id` pada tabel `events`, sehingga tidak ada cara membatasi acara per komunitas. Ini adalah horizontal privilege issue.
- **Race condition pada pre-check membership.** `CommunityController@store`, `MemberController@store`, dan `CommunityJoinController@store` memeriksa membership di luar transaksi, lalu memakai `updateOrCreate` di dalam transaksi. Unique `user_id` mencegah baris ganda, tetapi dua request bersamaan dapat saling menimpa `community_id`/`status` (last writer wins). Diperlukan `lockForUpdate` pada baris membership/user.
- **`CommunityMember` fillable terlalu luas.** `community_id`, `user_id`, `status`, dan `joined_at` semuanya fillable. Saat ini controller mengisi nilai server-side, tetapi model tidak melindungi dari mass assignment jika ada kode baru.
- **Perubahan status acara tidak dikunci.** `EventRsvpController` memeriksa `isFinished()` di luar transaksi. Admin dapat mengubah status menjadi `finished` bersamaan dengan RSVP yang sedang diproses, sehingga RSVP dapat tersimpan setelah acara ditandai selesai.
- **`MemberNumberService` pre-check di luar transaksi.** Jika dua pemanggilan bersamaan untuk user yang sama, keduanya dapat lolos `filled($user->member_number)` dan menulis nomor berbeda; unique constraint mencegah duplikasi, tetapi nilai akhir bisa berubah. Alur normal hanya memanggil sekali.

#### 3.2.3 Repudiation (R)

**R-1. Audit logger terpusat.**

`ActivityLogger::record` menulis `user_id`, `activity`, `description`, `ip_address`, dan `user_agent`. User ID diambil dari argumen atau `Auth::id()`. User agent dipotong 500 karakter.

```php
// app/Support/ActivityLogger.php 14–26
public static function record(
    string $activity,
    ?string $description = null,
    ?int $userId = null
): void {
    ActivityLog::create([
        'user_id' => $userId ?? Auth::id(),
        'activity' => $activity,
        'description' => $description,
        'ip_address' => request()->ip(),
        'user_agent' => substr((string) request()->userAgent(), 0, 500) ?: null,
    ]);
}
```

**R-2. Cakupan event audit.**

Audit dipanggil pada:

- `auth.register` (AuthController 61)
- `auth.login.failed` (83–86)
- `auth.login.success` (96–100)
- `auth.logout` (134–135)
- `community.created` (CommunityController 94–97)
- `community.leader.changed` (162–165)
- `community.join.requested` (CommunityJoinController 53–57)
- `community.membership.approved` (CommunityRequestController 56–59)
- `community.membership.rejected` (78–81)
- `community.member.added` (MemberController 80–83)
- `community.member.removed` (109–112)
- `event.created` (EventController 48–52)
- `event.updated` (109–113)
- `event.deleted` (125–129)
- `event.rsvp.updated` (EventRsvpController 55–60)
- `event.attendance.exported` (EventExportController 21–25)
- `profile.updated` (ProfileController 24–28)
- `member.role.changed` (Admin/UserController 58–63)
- `admin.leader_account.created` (AdminController 49–52)

**R-3. Immutability di level model.**

`ActivityLog` menolak update dan delete dengan mengembalikan `false` dari event listener. Ini mencegah perubahan melalui Eloquent, tetapi bukan melalui SQL langsung.

```php
// app/Models/ActivityLog.php 20–29
protected static function booted(): void
{
    static::updating(static function (): bool {
        return false;
    });

    static::deleting(static function (): bool {
        return false;
    });
}
```

**R-4. Celah Repudiation.**

- **Tidak ada UI audit log.** Admin tidak dapat melihat log melalui aplikasi; investigasi harus lewat database/CLI.
- **Tidak ada proteksi di level database.** User aplikasi dapat `UPDATE`/`DELETE` baris `activity_logs` jika memiliki hak SQL. Tidak ada trigger, permission granular, atau pengiriman ke sistem append-only.
- **Tidak ada retensi/arsip.** Log dapat tumbuh tanpa batas atau hilang saat rotasi/backup tidak terkelola.
- **Aktivitas gagal audit tidak dicatat sebagai kegagalan terpisah.** `ActivityLogger` tidak memakai try/catch; jika insert log gagal, operasi bisnis ikut gagal (fail-closed). Ini bisa menjadi masalah ketersediaan, bukan sekadar audit.
- **Actor dapat menjadi `null`.** `activity_logs.user_id` memakai `nullOnDelete`; jika user dihapus, jejak actor hilang. Tidak ada snapshot nama/email actor di log.

#### 3.2.4 Information Disclosure (I)

**I-1. Security headers aplikasi.**

Middleware `SecurityHeaders` menambahkan header pada semua response, termasuk response error (karena middleware global membungkus pipeline). Header hanya diisi jika belum ada, sehingga Nginx dapat menimpa/menghapusnya.

```php
// app/Http/Middleware/SecurityHeaders.php 15–29
$headers = [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'DENY',
    'Referrer-Policy' => 'strict-origin-when-cross-origin',
    'Permissions-Policy' => 'geolocation=(), camera=(), microphone=()',
    'Cross-Origin-Opener-Policy' => 'same-origin',
    'Cross-Origin-Resource-Policy' => 'same-origin',
    'X-Permitted-Cross-Domain-Policies' => 'none',
];
```

**I-2. CSP enforcing.**

CSP dibangun tanpa `'unsafe-inline'`. Pada environment `local` dan jika `public/hot` ada, origin Vite `http://localhost:5173`/`127.0.0.1:5173` dan WebSocket-nya diizinkan. Di production hanya `'self'`.

```php
// SecurityHeaders.php 41–52
$contentSecurityPolicy = implode('; ', [
    "default-src 'self'",
    'script-src '.implode(' ', $scriptSources),
    "style-src 'self'",
    "img-src 'self' data:",
    "font-src 'self'",
    "object-src 'none'",
    "base-uri 'self'",
    "form-action 'self'",
    "frame-ancestors 'none'",
    'connect-src '.implode(' ', $connectSources),
]);
```

**I-3. HSTS dan no-store.**

HSTS hanya dikirim jika `$request->isSecure()`. Response untuk user login selalu diberi `Cache-Control: no-store, private`, sehingga halaman berisi data pribadi tidak disimpan browser/proxy.

**I-4. Pembatasan path di Nginx.**

Nginx menolak dotfile (kecuali `.well-known`), file konfigurasi/dependency, ekstensi berbahaya, dan direktori internal. `fastcgi_hide_header` menghapus header aplikasi yang mungkin duplikat, lalu Nginx menambahkan header sendiri.

```nginx
# docker/nginx/default.conf 32–46
location ~ /\.(?!well-known).* { deny all; }

location ~* /(?:composer\.(?:json|lock)|package(?:-lock)?\.json|phpunit\.xml|\.env(?:\..*)?|\.git|artisan|Jenkinsfile) {
    deny all;
}

location ~* \.(?:bak|old|orig|save|swp|swo|sql|ini|log|md|pdf|drawio|ya?ml|conf|sh|lock)$ {
    deny all;
}

location ~* /(?:storage|bootstrap|config|database|resources|routes|tests|vendor|node_modules)/ {
    deny all;
}
```

**I-5. Apache dan docroot.**

`public/.htaccess` menambahkan header yang sama dan menolak file tersembunyi. `public/index.php` adalah satu-satunya front controller; tidak ada file aplikasi di docroot.

**I-6. Minimalisasi data API dan model.**

API hanya mengembalikan `id`, `name`, `email`, `role`. `User` menyembunyikan `password` dan `remember_token`; password di-hash dengan cast `hashed`.

**I-7. Konfigurasi produksi.**

`.env.example` menetapkan `APP_DEBUG=false`, `LOG_LEVEL=warning`, dan `APP_KEY` kosong (wajib di-generate). Dockerfile mematikan `expose_php` dan `display_errors`, serta mengaktifkan `log_errors`.

**I-8. Secret dan build context.**

`.gitignore` mengabaikan `.env`, `.env.*`, `*.sql`, `database/backups/`, `storage/logs/*`, dan hasil scan. `.dockerignore` mengecualikan `.env`, `.git`, `vendor`, `node_modules`, `storage`, `database/backups`, `docs`, `postman`, dan `tests` dari build context.

**I-9. Celah Information Disclosure.**

- **`opencode.zip` ter-track di Git.** Ukuran ~98,9 MB dan bersifat biner. Isinya tidak diperiksa dalam audit ini; arsip semacam ini berpotensi memuat konfigurasi lokal, token, atau riwayat sesi. Sebaiknya dihapus dari repository dan history, atau setidaknya diperiksa dengan secret scanner.
- **TLS tidak disediakan aplikasi/Compose.** Nginx hanya listen port 80. HSTS dan cookie `Secure` bergantung pada deteksi HTTPS; tanpa reverse proxy TLS dan `TrustProxies`, keduanya tidak efektif. README memang menyebut reverse proxy, tetapi konfigurasinya tidak ada di repo.
- **`TrustProxies` tidak dikonfigurasi.** Di balik load balancer/reverse proxy, `request()->ip()` dapat berisi IP proxy, sehingga rate limit per IP dan audit IP menjadi tidak akurat. `request()->isSecure()` juga dapat salah.
- **PII di audit log.** IP dan user-agent disimpan tanpa kebijakan retensi/anonymization. Ini dapat menjadi risiko privasi.
- **Log aplikasi.** `LOG_CHANNEL=stack`, `LOG_STACK=daily`; file log berada di `storage/logs`. Nginx menolak path `storage`, tetapi akses filesystem lokal tetap perlu dibatasi.
- **`APP_URL` contoh HTTP.** Produksi harus mengganti ke HTTPS; tidak ada validasi otomatis.

#### 3.2.5 Denial of Service (D)

**D-1. Rate limiter aplikasi.**

`AppServiceProvider` mendefinisikan 11 limiter: `login`, `registration`, `community-join`, `rsvp`, `profile-update`, `event-write`, `event-export`, `member-role`, `admin-actions`, `leader-actions`, dan `api`. Semua memakai key user ID (jika login) atau IP.

```php
// AppServiceProvider.php 45–51
RateLimiter::for('event-write', function (Request $request) {
    return Limit::perMinute(15)->by((string) ($request->user()?->id ?? $request->ip()));
});

RateLimiter::for('event-export', function (Request $request) {
    return Limit::perMinute(5)->by((string) ($request->user()?->id ?? $request->ip()));
});
```

**D-2. Rate limit dan batas Nginx.**

`limit_req_zone` 10 r/s per IP; `burst=20 nodelay`; status 429. Body maksimum 2 MB; timeout header/body/send 10 detik; keepalive 15 detik. Ini membatasi slowloris sederhana dan payload besar.

**D-3. Pagination dan batas kandidat.**

Daftar acara 10/halaman, kehadiran 25/halaman, anggota 25/halaman, komunitas 15/halaman, permintaan 20/halaman, user 25/halaman. Kandidat ketua/anggota dibatasi `limit(500)`. Pencarian dibatasi 100 karakter.

**D-4. Retry transaksi.**

`EventRsvpController` memakai `DB::transaction(..., 3)` dan `MemberNumberService` memakai retry 3, sehingga deadlock singkat dapat dipulihkan.

**D-5. Celah DoS.**

- **Tidak ada CAPTCHA.** Registrasi dan login tetap dapat dibanjiri dari banyak IP.
- **Rate limit bergantung database.** `CACHE_STORE=database`; jika tabel cache lambat, limiter memperlambat request itu sendiri. Tidak ada Redis/Memcached.
- **Tidak ada worker queue.** `QUEUE_CONNECTION=database` tetapi Compose tidak menjalankan `queue:work`; job yang dibuat (jika ada di masa depan) akan menumpuk.
- **Export tidak dibatasi jumlah baris.** Satu acara dengan puluhan ribu RSVP dapat menghasilkan XLSX besar di memori (seluruh konten dibangun sebagai string). Limiter 5/menit tidak membatasi ukuran per request.
- **Tidak ada proteksi DDoS volumetrik.** Nginx rate limit per IP dapat dilewati dengan IP terdistribusi; tidak ada CDN/WAF/autoscaling.
- **Batas body hanya di Nginx.** Jika aplikasi dijalankan via `php artisan serve` atau web server lain, `client_max_body_size` tidak berlaku.

#### 3.2.6 Elevation of Privilege (E)

**E-1. RBAC middleware.**

`CheckRole` menolak guest (redirect login) dan user dengan role di luar daftar (403). Route admin dan ketua memakai grup middleware terpisah.

```php
// app/Http/Middleware/CheckRole.php 11–25
public function handle(Request $request, Closure $next, string ...$roles): Response
{
    if (!$request->user()) {
        return redirect()->route('login');
    }

    if (!in_array($request->user()->role, $roles, true)) {
        abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
    }

    return $next($request);
}
```

**E-2. Policy acara.**

`EventPolicy` mengizinkan `viewAny`/`view` untuk semua user login, tetapi `create`/`update`/`delete`/`export` hanya untuk `admin`/`ketua` melalui `canManageEvents()`.

```php
// app/Policies/EventPolicy.php 20–38
public function create(User $user): bool
{
    return $user->canManageEvents();
}

public function update(User $user, Event $event): bool
{
    return $user->canManageEvents();
}

public function delete(User $user, Event $event): bool
{
    return $user->canManageEvents();
}

public function export(User $user, Event $event): bool
{
    return $user->canManageEvents();
}
```

**E-3. Role tidak dapat dimanipulasi dari registrasi/profil.**

`User::$fillable` tidak memuat `role`/`member_number`; `AuthController@register` tidak mengirim kedua field itu; `ProfileController@update` hanya memakai `$request->validated()` dari `UpdateProfileRequest` yang tidak memiliki aturan `role`/`member_number`.

**E-4. Admin tidak dapat mengubah role sendiri.**

```php
// app/Http/Controllers/Admin/UserController.php 41–43
if ((int) $user->id === (int) $actor->id) {
    return back()->with('error', 'Admin tidak dapat mengubah role akunnya sendiri.');
}
```

**E-5. Akun ketua baru tidak menerima role dari client.**

`AdminController@storeLeaderAccount` hanya memvalidasi `leader_*`; input `role` diabaikan. User baru memakai default database `member`, lalu nomor anggota diberikan. Promosi ke `ketua` terjadi saat komunitas dibuat.

**E-6. Pemetaan nomor urut ke role hanya untuk alur tepercaya.**

`MemberNumberService::roleForSequence` memetakan urutan 1 → `admin`, 2 → `ketua`, sisanya `member`. Namun `assignTo` hanya menerapkan role jika argumen `$applySequenceRole` bernilai `true`. Registrasi publik memanggil `assignTo($user)` tanpa argumen (default `false`), sehingga user pertama yang mendaftar tetap `member`. Seeder admin memanggil `assignTo($user, true)`.

```php
// MemberNumberService.php 67–74
public function roleForSequence(int $sequence): string
{
    return match ($sequence) {
        1 => 'admin',
        2 => 'ketua',
        default => 'member',
    };
}
```

**E-7. Celah Elevation of Privilege.**

- **Ketua dapat mengelola semua acara.** Tidak ada pembatasan kepemilikan komunitas pada acara. Ketua A dapat mengedit/menghapus acara yang dibuat Ketua B, serta mengekspor daftar hadirnya. Ini adalah horizontal privilege escalation.
- **Tidak ada perlindungan “admin terakhir”.** Admin dapat menurunkan admin lain menjadi member/ketua. Jika hanya tersisa satu admin dan ia menurunkan dirinya sendiri, aksi itu ditolak; tetapi jika ada dua admin, satu dapat menurunkan yang lain, lalu admin tersisa dapat menurunkan dirinya? Tidak, dirinya ditolak. Jadi selalu tersisa minimal satu admin selama aturan self-change aktif. Namun tidak ada jaminan eksplisit jika data diubah langsung di DB.
- **Tidak ada MFA untuk admin.** Takeover akun admin hanya membutuhkan password.
- **`CommunityMember` mass assignment.** `status` dan `community_id` fillable; jika ada controller baru yang meneruskan input mentah, user dapat memilih komunitas/status.
- **Token API tanpa ability/expiry.** Jika token mulai diterbitkan, token tersebut memiliki akses penuh ke route yang dilindungi `auth:sanctum` dan tidak kedaluwarsa.
- **Admin dapat mempromosikan siapa pun menjadi admin.** Ini fitur yang disengaja, tetapi tidak ada dual-control/konfirmasi kedua. Risiko penyalahgunaan oleh admin yang compromised.
- **`EventPolicy` tidak memakai `created_by`.** Bahkan acara yang dibuat admin dapat diedit ketua mana pun.

#### 3.2.7 Kategori yang Tidak Memiliki Mitigasi Sama Sekali

Kategori/area berikut secara eksplisit tidak memiliki kontrol yang memadai di repository:

1. **MFA** untuk semua peran, terutama admin.
2. **Reset password** (route/controller tidak ada).
3. **Verifikasi email** (tidak diberlakukan).
4. **CAPTCHA/anti-bot** pada registrasi/login.
5. **Proteksi DDoS volumetrik / WAF / CDN** (hanya Nginx rate limit).
6. **TLS termination** di repository (hanya HTTP port 80; reverse proxy eksternal tidak dikonfigurasi).
7. **Trusted proxies** (`TrustProxies`) untuk deployment di balik proxy.
8. **Immutability audit log di level database** (trigger/permission).
9. **Retensi/anonymization PII** pada audit log.
10. **Secret scanning / dependency audit otomatis** di CI (tidak ada `composer audit`, `npm audit`, atau gitleaks).
11. **Pemeriksaan isi `opencode.zip`** (artefak biner besar ter-track).

<!-- BAGIAN_4 -->
