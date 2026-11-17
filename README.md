# Unknown Rider Web

Aplikasi Laravel untuk pengelolaan komunitas Unknown Rider. Versi ini menghapus fungsi Live GPS Tracking dan menambahkan sejumlah mitigasi keamanan berdasarkan STRIDE.

## Persyaratan

- PHP 8.3 atau lebih baru
- Composer
- Node.js dan npm
- MySQL 8 (atau database yang didukung Laravel)
- Docker dan Docker Compose (opsional)

## Menjalankan secara lokal

1. Salin `.env.example` menjadi `.env`.
2. Isi `DB_PASSWORD` dan `DB_ROOT_PASSWORD` dengan password acak yang kuat (Docker akan menolak start bila nilainya kosong). Jangan commit file `.env`.
3. Jalankan `composer install` dan `npm install`.
4. Buat application key: `php artisan key:generate`.
5. Pastikan MySQL hidup dan nilai koneksi database di `.env` sesuai.
6. Buat backup database terlebih dahulu bila ini instalasi lama, karena migration GPS akan menghapus tabel riwayat koordinat. Lalu jalankan migration: `php artisan migrate`.
7. Build asset: `npm run build`.
8. (Opsional) Isi `INITIAL_ADMIN_EMAIL` dan `INITIAL_ADMIN_PASSWORD` pada `.env`, lalu jalankan `php artisan db:seed --class=AdminUserSeeder` untuk membuat akun admin pertama.
9. Jalankan aplikasi: `php artisan serve`.

## Menjalankan dengan Docker

1. Salin `.env.example` menjadi `.env` dan isi kedua password database dengan password acak yang kuat.
2. Jalankan `composer install` dan `npm install && npm run build` pada project.
3. Jalankan `php artisan key:generate`.
4. Jalankan `docker compose up -d --build`.
5. Jalankan migration di container aplikasi: `docker compose exec app php artisan migrate --force`.
6. (Opsional) Isi `INITIAL_ADMIN_EMAIL` dan `INITIAL_ADMIN_PASSWORD` pada `.env`, lalu jalankan `docker compose exec app php artisan db:seed --class=AdminUserSeeder`.
7. Buka `http://localhost:8000`.

Untuk volume database lama, variabel `MYSQL_*` hanya digunakan saat volume MySQL pertama kali dibuat. Jangan hapus volume sebelum backup. Jika volume lama masih menggunakan akun root, buat akun aplikasi `unknown_rider_app` dengan hak akses hanya ke database `rockbikers_db`, lalu samakan nilainya dengan `DB_USERNAME` dan `DB_PASSWORD`. Database hanya tersedia di jaringan internal Docker dan tidak dipublikasikan ke host. Web hanya di-bind ke `127.0.0.1` untuk lingkungan pengembangan. Atur reverse proxy dan HTTPS sebelum deployment publik, lalu set `APP_ENV=production`, `APP_DEBUG=false`, dan `SESSION_SECURE_COOKIE=true`.

## Pengujian

Jalankan `php artisan test`. Tes keamanan yang ditambahkan memeriksa akses dashboard, pembatasan role, perlindungan dari mass assignment role saat registrasi, tidak tersedianya endpoint GPS, security headers, dan path repository yang tidak boleh dapat diakses lewat HTTP.

## Fitur GPS yang dihapus

- Endpoint `/api/tracking/update` dan `/api/tracking/get` dihapus.
- Tracking controller dan model lokasi dihapus.
- UI peta, tombol mulai/stop GPS, Leaflet, dan pengiriman koordinat dari dashboard dihapus.
- Migration baru menghapus tabel `location_histories` dan `tracking_locations` dari database saat migration dijalankan.

Diagram pada folder `docs/design/` merupakan artefak desain lama dan masih memuat alur GPS; diagram tersebut belum diperbarui pada paket ini. Migration lama tetap dipertahankan sebagai riwayat perubahan skema. Migration penghapusan GPS tidak dapat memulihkan data koordinat yang telah dihapus saat di-rollback.

## Struktur dokumentasi dan keamanan

- `docs/SECURITY_STRIDE.md` — ringkasan threat model, mitigasi, dan catatan pengujian.
- `docs/design/` — diagram DFD, flowchart, dan use case.
- `docs/security/zap.yaml` — konfigurasi pemindaian OWASP ZAP. Hasil scan (`zap-report.*`) bersifat lokal dan tidak disimpan di repository.

File sensitif seperti `.env`, `vendor/`, `node_modules/`, `storage/logs`, database SQL, dan hasil scan tidak diikutsertakan ke Git maupun ke build context Docker (`.dockerignore`). Web server hanya menyajikan folder `public/`, sehingga kode aplikasi dan dokumentasi tidak dapat diakses dari browser. Security headers juga dipasang di level aplikasi (`app/Http/Middleware/SecurityHeaders.php`) sebagai lapisan cadangan ketika aplikasi dijalankan tanpa Nginx.

Lihat [docs/SECURITY_STRIDE.md](docs/SECURITY_STRIDE.md) untuk ringkasan threat model dan mitigasi.
