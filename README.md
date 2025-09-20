# Laravel Gemini Test

Project ini dibuat dengan **Laravel 11** sebagai backend framework.  
Repo ini digunakan untuk percobaan integrasi dan pengembangan fitur.

## 🚀 Fitur
- Authentication (Login, Register, Logout)
- Routing dengan Laravel 11
- Struktur project default Laravel
- Environment configuration menggunakan `.env`

## 🛠️ Persyaratan Sistem
- PHP >= 8.2
- Composer >= 2.x
- MySQL / MariaDB
- Node.js & NPM (opsional untuk frontend build)

## ⚙️ Instalasi

1. Clone repository:
   git clone https://github.com/farhan12376/laravel-gemini-test.git
   cd laravel-gemini-test

2. Install dependencies:
   composer install
   npm install
   npm run dev

3. Copy file environment:
   cp .env.example .env

4. Generate application key:
   php artisan key:generate

5. Migrasi database:
   php artisan migrate

6. Jalankan server lokal:
   php artisan serve

## 📂 Struktur Folder Penting
- app/ → berisi logic utama aplikasi
- routes/ → file route web & API
- database/ → migrasi & seeder
- resources/ → view & asset frontend
- public/ → root akses publik

## 📌 Catatan
- Jangan lupa atur konfigurasi `.env` sesuai environment (database, mail, dll).
- File `.env`, `vendor/`, dan `node_modules/` sudah di-ignore di Git.

## 👨‍💻 Author
Farhan Septian  
[GitHub](https://github.com/farhan12376)