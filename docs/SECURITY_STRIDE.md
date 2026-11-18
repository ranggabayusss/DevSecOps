# STRIDE Security Review — Unknown Rider

## Ruang lingkup

Review ini dilakukan terhadap source code yang tersedia pada repository. Perubahan yang diterapkan berfokus pada penghapusan live GPS tracking dan mitigasi ancaman STRIDE pada autentikasi, pengelolaan komunitas, database, API, serta konfigurasi Docker/Nginx.

## Ringkasan ancaman dan mitigasi

| STRIDE | Risiko yang ditemukan | Perubahan yang diterapkan |
|---|---|---|
| **Spoofing identity** | Endpoint GPS lama menerima request tanpa autentikasi. Proteksi sesi dan pembatasan percobaan login belum diperketat. | Endpoint GPS dihapus; login dibatasi per kombinasi email/IP dan per IP; session ID diregenerasi setelah login; cookie session diatur HttpOnly dan terenkripsi, serta Secure otomatis di production. |
| **Tampering with data** | Endpoint GPS lama menerima koordinat dari request, memakai `user_id` dan `tour_id` tetap, serta menonaktifkan foreign key checks. Field role juga termasuk mass-assignable pada model User. | Endpoint/controller/model GPS dihapus; foreign-key bypass ikut hilang; role dikeluarkan dari daftar mass assignment; role hanya diubah oleh alur server-side; validasi input dan transaksi database tetap digunakan; migration status membership diperbaiki. |
| **Repudiation** | Tabel `activity_logs` tersedia tetapi belum digunakan oleh alur aplikasi. | Menambahkan ActivityLogger dan pencatatan login berhasil/gagal, registrasi, logout, pembuatan komunitas, permintaan/keputusan keanggotaan, pergantian ketua, serta penambahan/penghapusan anggota. Log menyimpan actor ID, IP, user-agent, waktu, aktivitas, dan deskripsi ringkas. |
| **Information disclosure** | Endpoint GPS `GET` lama dapat mengungkap lokasi terkini semua pengguna tanpa autentikasi. `.env.example` berisi APP_KEY dan kredensial database contoh tetap, dan debug aktif. | Endpoint GPS dihapus; API user mengembalikan field minimum untuk pengguna yang terautentikasi; APP_KEY dikosongkan pada contoh konfigurasi; APP_DEBUG false; kredensial database harus diisi sendiri; Nginx menolak akses file tersembunyi dan mengirim beberapa security headers. |
| **Denial of service (DoS)** | Request login/registrasi dan daftar data belum memiliki pembatasan yang jelas; daftar komunitas/anggota ditarik tanpa pagination. Database juga dipublikasikan ke port host Docker. | Rate limit pada login, registrasi, join komunitas, admin actions, dan API; pagination pada daftar komunitas, anggota, dan permintaan; batas hasil daftar calon anggota; MySQL hanya berada di jaringan internal Docker. |
| **Elevation of privilege** | `role` dapat diisi melalui mass assignment model User; admin/ketua memiliki operasi sensitif yang perlu pembatasan server-side. | `role` dikeluarkan dari fillable User; registrasi tidak menerima role dari request; route admin tetap memakai `auth` + `role:admin`; operasi Ketua memverifikasi bahwa pengguna yang login adalah leader komunitas dan membership berasal dari komunitas yang sama. |

## GPS tracking yang dihapus

- Route `POST /api/tracking/update` dan `GET /api/tracking/get` sudah dihapus.
- `TrackingController` dan model `TrackingLocation` sudah dihapus.
- Dashboard sudah diganti tanpa Leaflet, peta, tombol tracking, browser Geolocation API, maupun `fetch()` koordinat.
- Header Nginx mengatur `Permissions-Policy: geolocation=()` agar browser tidak memberikan akses geolokasi kepada aplikasi.
- Migration baru menghapus tabel `location_histories` dan `tracking_locations` ketika `php artisan migrate` dijalankan. Backup database sebelum menerapkan migration pada data yang sudah berjalan.

Migration pembuat tabel lama sengaja tetap disimpan sebagai jejak historis migration Laravel. Migration baru berjalan setelah migration lama dan menghapus tabel tersebut. Rollback hanya dapat membuat ulang tabel kosong; data GPS yang sudah dihapus tidak dapat dipulihkan.

## Pengerasan paparan file dan konfigurasi

Lapisan berikut memastikan file proyek tidak dapat diakses langsung dari browser maupun terbawa ke artefak build:

- **Docroot terbatas pada `public/`.** Nginx dan Apache hanya menyajikan `public/`; `.env`, `app/`, `config/`, `database/`, `storage/`, `vendor/`, `tests/`, dan dokumen internal berada di luar jangkauan HTTP.
- **Middleware `SecurityHeaders`.** Header `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, `Cross-Origin-Opener-Policy`, `Cross-Origin-Resource-Policy`, `X-Permitted-Cross-Domain-Policies`, dan CSP report-only dipasang di level aplikasi; HSTS ditambahkan otomatis pada request HTTPS.
- **Nginx.** `server_tokens off`, blokir dotfile, file sensitif (`composer.*`, `package*.json`, `phpunit.xml`, `.env`), ekstensi berbahaya (`.sql`, `.log`, `.bak`, `.old`, `.conf`, `.yaml`, `.md`, `.pdf`, `.drawio`), metode HTTP selain GET/HEAD/POST/PUT/PATCH/DELETE/OPTIONS, serta `fastcgi_hide_header X-Powered-By`.
- **PHP-FPM.** `expose_php=Off`, `display_errors=Off`, `log_errors=On`, `cgi.fix_pathinfo=0`, `session.use_strict_mode=1`, cookie sesi HttpOnly + SameSite, dan `allow_url_include=Off`.
- **Apache.** `public/.htaccess` menambahkan security headers, mematikan `X-Powered-By`, dan menolak file tersembunyi.
- **Docker.** `.dockerignore` mengecualikan `.env`, `.git`, `vendor`, `node_modules`, storage, database lokal, hasil scan, dan dokumentasi dari build context.
- **Repository.** `zap-report.*`, `database/backups/`, `*.sql`, `.env*`, dan artefak lokal diabaikan oleh Git. `robots.txt` menolak seluruh crawling.

Seluruh path sensitif juga diuji melalui `tests/Feature/SecurityHeadersTest.php` (header keamanan, response error, dan path repository yang tidak boleh routable).

## Pengujian yang disertakan

`tests/Feature/StrideSecurityTest.php` mencakup pengujian bahwa endpoint tracking tidak tersedia, role yang dikirim saat registrasi tidak dapat menaikkan hak akses, dashboard memerlukan login, dan endpoint admin menolak pengguna biasa.

`tests/Feature/SecurityHeadersTest.php` mencakup pengujian security headers pada response normal dan error, serta memastikan path seperti `.env`, `composer.json`, `phpunit.xml`, log, dan database lokal tidak dapat diakses melalui HTTP.

Jalankan:

```bash
php artisan test
```

## Keanggotaan, acara, RSVP, dan export Excel

Fitur baru menambahkan tabel `member_number_counters`, kolom profil pada `users` (`member_number` unik, `motorcycle_type`, `license_plate`), tabel `events`, dan tabel `event_rsvps` dengan constraint unik `(event_id, user_id)`.

Nomor anggota dibuat oleh `App\Support\MemberNumberService` di dalam transaksi database dengan `lockForUpdate()` pada baris counter. Formatnya `[prefix]-[urut]`, contoh `unrider-01`. Prefix berasal dari `MEMBER_NUMBER_PREFIX` (default `unrider`). Migration backfill memberi nomor pada pengguna lama dan mengurutkan admin, ketua, lalu member agar data awal tetap konsisten. Pendaftaran publik **selalu** menghasilkan role `member`, walaupun nomor urutnya 1 atau 2; pemetaan urutan ke role admin/ketua hanya dipakai pada alur provisioning tepercaya (seeder/admin) untuk mencegah elevation of privilege lewat registrasi publik. Admin dapat mengubah role melalui halaman Manajemen Anggota.

| STRIDE | Implementasi pada fitur baru |
|---|---|
| **Spoofing** | RSVP dan edit profil memakai `$request->user()` dari sesi; `user_id`, `event_id`, `role`, dan `member_number` dari body diabaikan. Form memakai CSRF token Laravel, password tetap di-hash, dan session ID diregenerasi setelah login. |
| **Tampering** | `User::$fillable` hanya memuat field profil yang aman; `role` dan `member_number` diisi lewat `forceFill` server-side. Nomor anggota memakai transaksi + `lockForUpdate` agar tidak duplikat. Eloquent/parameter binding mencegah SQL injection, Blade meng-escape output, dan sel Excel yang diawali `=`, `+`, `-`, `@`, tab, atau CR diberi prefix `'` oleh `SpreadsheetValueSanitizer`. |
| **Repudiation** | `events.created_by`/`updated_by`, `event_rsvps.confirmed_at`, serta `activity_logs` mencatat pembuatan/ubah/hapus acara, perubahan role, perubahan RSVP, export Excel, dan update profil beserta aktor, IP, dan user-agent. |
| **Information disclosure** | Halaman acara dan daftar anggota memerlukan login; export dan halaman anggota memakai RBAC admin/ketua. Detail acara memakai `Cache-Control: no-store`. Error mengikuti `APP_DEBUG=false` sehingga stack trace tidak ditampilkan. Daftar anggota dan kehadiran dipaginasi. |
| **Denial of service** | Rate limiter `rsvp`, `event-write`, `event-export`, `profile-update`, dan `member-role` ditambahkan; daftar acara, kehadiran, dan anggota memakai pagination. Export dibatasi 5 permintaan/menit per pengguna. |
| **Elevation of privilege** | Route admin/ketua dilindungi middleware `role`, `EventPolicy`, dan otorisasi FormRequest. Hanya Admin yang boleh mengubah role; Admin tidak dapat mengubah role akunnya sendiri agar tidak terjadi lockout. |

Export `.xlsx` dibuat oleh `App\Support\XlsxWriter` tanpa ketergantungan ekstensi `zip`, menggunakan format OpenXML dan ZIP stored-entry murni PHP. Nama file dibersihkan menjadi `Kehadiran_[Judul]_[Tanggal].xlsx` sehingga tidak dapat dipakai untuk header injection.

Pengujian baru tersedia di `tests/Feature/EventMembershipTest.php` dan `tests/Unit/SpreadsheetValueSanitizerTest.php`, mencakup auto-number, penolakan field istimewa saat registrasi, RBAC acara/export/role, RSVP tanpa `user_id` dari client, penguncian RSVP saat acara selesai, formula injection pada Excel, dan rate limit export.

## Batasan dan pekerjaan lanjutan

Mitigasi pada aplikasi tidak dapat menjamin perlindungan terhadap volumetric DDoS atau serangan pada infrastruktur di luar Laravel. Untuk deployment publik, gunakan HTTPS, reverse proxy/WAF atau CDN dengan rate limiting, firewall, backup database, monitoring, dan pembaruan dependency berkala.

Activity log aplikasi membantu investigasi dan akuntabilitas, tetapi bukan bukti non-repudiation yang tahan manipulasi terhadap administrator database. Untuk kebutuhan audit formal, kirim log ke sistem terpisah dengan append-only/immutable retention dan batasi akses database.

Pengujian di paket ini bersifat source-level dan feature tests. Hasil runtime lengkap masih harus dikonfirmasi pada environment yang memiliki dependency Composer terpasang dan database yang aktif.
