# SAW Employee Evaluation System

<p align="center">
    <img src="https://img.shields.io/badge/Laravel-12.0-FF2D20?style=for-the-badge&logo=laravel" alt="Laravel 12.0">
    <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php" alt="PHP 8.2+">
    <img src="https://img.shields.io/badge/Vite-7.0-646CFF?style=for-the-badge&logo=vite" alt="Vite">
    <img src="https://img.shields.io/badge/TailwindCSS-3.1-06B6D4?style=for-the-badge&logo=tailwindcss" alt="TailwindCSS">
    <img src="https://img.shields.io/badge/Alpine.js-3.4-8BC34A?style=for-the-badge&logo=alpine.js" alt="Alpine.js">
</p>

## Tentang Aplikasi

Sistem Evaluasi Karyawan SAW (Simple Additive Weighting) adalah aplikasi web yang dirancang untuk mengelola dan mengevaluasi kinerja karyawan menggunakan metode SAW. Sistem ini menyediakan fitur-fitur canggih untuk analisis kinerja, pelaporan, dan manajemen data karyawan.

### ✨ Fitur Utama

- **🏠 Dashboard Interaktif** - Visualisasi data dengan grafik dan statistik real-time
- **👥 Manajemen Karyawan** - CRUD lengkap dengan import/export Excel & PDF
- **📊 Kriteria Evaluasi** - Pengaturan bobot kriteria dengan validasi otomatis
- **🎯 Sistem Evaluasi** - Penilaian batch dan individual dengan metode SAW
- **📈 Analisis Lanjutan** - Sensitivity analysis, What-if scenarios, dan forecasting
- **📄 Laporan Komprehensif** - Export PDF dan Excel dengan berbagai format
- **🌐 Multi-bahasa** - Dukungan internationalization (i18n)
- **🔐 Autentikasi & Autorisasi** - Laravel Breeze dengan role-based access
- **⚡ Queue System** - Background job processing untuk performa optimal
- **🔧 Admin Panel** - Monitoring sistem, cache management, dan health check

## 🚀 Persyaratan Sistem

- **PHP**: 8.2 atau lebih tinggi
- **Composer**: 2.0 atau lebih tinggi
- **Node.js**: 18.0 atau lebih tinggi
- **NPM**: 8.0 atau lebih tinggi
- **Database**: SQLite (default) atau MySQL/PostgreSQL
- **Web Server**: Apache, Nginx, atau PHP built-in server

## 📦 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/username/SAWLaravel.git
cd SAWLaravel
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup

```bash
# Create SQLite database (default)
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed database with sample data (optional)
php artisan db:seed
```

### 5. Build Assets

```bash
# Build frontend assets for development
npm run dev

# Or build for production
npm run build
```

## 🏃‍♂️ Menjalankan Aplikasi

### Development Mode (Recommended)

Gunakan script composer yang sudah dikonfigurasi untuk menjalankan semua service sekaligus:

```bash
composer run dev
```

Script ini akan menjalankan:
- Laravel development server (http://localhost:8000)
- Queue worker untuk background jobs
- Log monitoring dengan Laravel Pail
- Vite dev server untuk hot reload

### Manual Development

Jika ingin menjalankan service secara terpisah:

```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Queue worker
php artisan queue:work

# Terminal 3: Frontend assets (hot reload)
npm run dev

# Terminal 4: Log monitoring (optional)
php artisan pail
```

### Production Mode

```bash
# Build production assets
npm run build

# Configure web server to point to public/ directory
# Set proper file permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Setup queue worker as daemon (systemd/supervisor)
php artisan queue:work --daemon
```

## ⚙️ Konfigurasi

### Database Configuration

**SQLite (Default):**
```env
DB_CONNECTION=sqlite
# File database akan dibuat di database/database.sqlite
```

**MySQL:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saw_evaluation
DB_USERNAME=root
DB_PASSWORD=your_password
```

**PostgreSQL:**
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=saw_evaluation
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### Queue Configuration

Untuk performa optimal, pastikan queue worker berjalan:

```env
QUEUE_CONNECTION=database
DB_QUEUE_TABLE=jobs
DB_QUEUE_RETRY_AFTER=300
```

### Mail Configuration

```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-server
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourcompany.com"
MAIL_FROM_NAME="SAW Evaluation System"
```

## 👤 Akun Default

Setelah menjalankan seeder, Anda dapat login dengan:

- **Admin**: admin@example.com / password
- **User**: user@example.com / password

## 🔧 Command Artisan Berguna

```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate
php artisan migrate:fresh --seed

# Queue management
php artisan queue:work
php artisan queue:restart
php artisan queue:failed

# Create storage link
php artisan storage:link

# Run tests
php artisan test
```

## 📊 Fitur Analisis Lanjutan

Sistem ini menyediakan berbagai analisis canggih:

### Sensitivity Analysis
Menganalisis sensitivitas hasil evaluasi terhadap perubahan bobot kriteria.

### What-if Scenarios
Simulasi berbagai skenario untuk memprediksi dampak perubahan kriteria.

### Multi-Period Comparison
Perbandingan kinerja karyawan across multiple periods.

### Performance Forecasting
Prediksi kinerja masa depan berdasarkan trend historis.

## 📈 Export & Import

### Export Features
- **PDF Reports**: Laporan detail dengan grafik dan analisis
- **Excel Export**: Data dalam format spreadsheet
- **Template Download**: Template untuk import data

### Import Features
- **Employee Data**: Import data karyawan dari Excel
- **Criteria Data**: Import kriteria evaluasi
- **Evaluation Data**: Import hasil evaluasi batch

## 🛠️ Troubleshooting

### Common Issues

**1. Permission Errors:**
```bash
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R $USER:www-data storage bootstrap/cache
```

**2. Database Connection Error:**
```bash
# Check database file exists (SQLite)
ls -la database/database.sqlite

# Or test MySQL connection
php artisan tinker
DB::connection()->getPdo();
```

**3. Queue Jobs Not Processing:**
```bash
# Restart queue worker
php artisan queue:restart
php artisan queue:work --verbose
```

**4. Assets Not Loading:**
```bash
# Rebuild assets
npm run build
php artisan config:clear
```

**5. Email Not Sending:**
```bash
# Test email configuration
php artisan tinker
Mail::raw('Test email', function($msg) { $msg->to('test@example.com'); });
```

## 🏗️ Arsitektur Sistem

```
├── app/
│   ├── Http/Controllers/     # Controllers
│   ├── Models/              # Eloquent Models
│   ├── Services/            # Business Logic
│   ├── Exports/             # Export Classes
│   ├── Imports/             # Import Classes
│   └── Jobs/                # Queue Jobs
├── database/
│   ├── migrations/          # Database Migrations
│   ├── seeders/            # Database Seeders
│   └── factories/          # Model Factories
├── resources/
│   ├── views/              # Blade Templates
│   ├── js/                 # Frontend JavaScript
│   └── css/                # Stylesheets
└── routes/
    ├── web.php             # Web Routes
    └── auth.php            # Authentication Routes
```

## 🤝 Contributing

1. Fork repository ini
2. Buat feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 📝 License

Aplikasi ini menggunakan lisensi [MIT License](https://opensource.org/licenses/MIT).

## 🆘 Support

Jika Anda mengalami masalah atau membutuhkan bantuan:

1. Periksa [Issues](https://github.com/username/SAWLaravel/issues) yang sudah ada
2. Buat issue baru dengan deskripsi detail
3. Sertakan informasi environment dan log error

## 📞 Contact

- **Developer**: Your Name
- **Email**: your.email@example.com
- **GitHub**: [@yourusername](https://github.com/yourusername)

---

<p align="center">
Dibuat dengan ❤️ menggunakan Laravel Framework
</p>
