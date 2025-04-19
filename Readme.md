# Herodigitaltest

## Deskripsi Proyek

Proyek ini merupakan aplikasi booking rental ps4 dan ps5 digunakan sebagai technical test

## Struktur Direktori

- **`app/`** - Berisi logika bisnis aplikasi, termasuk model dan controller.
- **`bootstrap/`** - Mengelola proses bootstrap aplikasi.
- **`config/`** - Berisi file konfigurasi aplikasi.
- **`database/`** - Mengelola migrasi dan seeder basis data.
- **`public/`** - Berisi file publik seperti `index.php` dan aset statis.
- **`resources/`** - Berisi view, file bahasa, dan aset yang belum dikompilasi.
- **`routes/`** - Berisi definisi rute aplikasi.
- **`storage/`** - Menyimpan file yang digunakan oleh aplikasi seperti cache dan log.
- **`tests/`** - Berisi unit test dan feature test.

## Persyaratan Sistem

Pastikan Anda telah menginstal perangkat lunak berikut:

- **PHP** ≥ 8.0
- **Composer** (untuk mengelola dependensi PHP)
- **Node.js & NPM** (untuk mengelola dependensi front-end)
- **Database** (MySQL/PostgreSQL, sesuai konfigurasi proyek)

## Instalasi

1. **Kloning Repository**

    ```sh
    git clone https://github.com/muhfurqans35/herodigitaltest.git
    ```

2. **Masuk ke Direktori Proyek**

    ```sh
    cd herodigitaltest
    ```

3. **Instal Dependensi PHP**

    ```sh
    composer install
    ```

4. **Instal Dependensi JavaScript**

    ```sh
    npm install
    ```

5. **Salin File Konfigurasi Lingkungan**

    ```sh
    cp .env.example .env
    ```

6. **Generate Kunci Aplikasi**

    ```sh
    php artisan key:generate
    ```

7. **Konfigurasi Basis Data**

    - Edit `.env` dan sesuaikan dengan kredensial database Anda:
        ```env
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=nama_database
        DB_USERNAME=username
        DB_PASSWORD=password
        ```

8. **Jalankan Migrasi Database**

    ```sh
    php artisan migrate --seed
    ```

9. **Kompilasi Aset Front-end**

    ```sh
    npm run dev
    ```

10. **Jalankan Server Lokal**
    ```sh
    php artisan serve
    ```
    Akses aplikasi melalui [http://localhost:8000](http://localhost:8000).

## Fitur Utama

- Autentikasi pengguna
- CRUD booking, user, role, service, booking, review
- Integrasi midtrans
- Import/export excel dengan dynamic model dan field.
- Audit

## User Admin

- Username: admin@example.com
- Password: admin123
