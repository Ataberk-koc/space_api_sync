# 🚀 SpaceX API Sync - Kurulum ve Yapılandırma Kılavuzu

## ✅ Tamamlanan Gereksinimler

### ✅ Back-end Gereksinimleri (Tamamlandı)

1. ✅ **SpaceX API'sinden Veri Senkronizasyonu**
   - Artisan komutu: `php artisan spacex:sync`
   - Otomatik çalışma: Her 3 dakikada bir
   - Lokasyon: `app/Console/Commands/SyncSpaceXData.php`

2. ✅ **Event/Listener ve E-posta Bildirimi**
   - Event: `App\Events\DataSyncCompleted`
   - Listener: `App\Listeners\SendSyncNotification`
   - Notification: `App\Notifications\SyncCompletedNotification`
   - Kayıt: `app/Providers/AppServiceProvider.php`

3. ✅ **Loglama**
   - Log dosyası: `storage/logs/laravel.log`
   - Format: JSON (structured logging)

4. ✅ **API Endpoints**
   - `GET /api/capsules` - Tüm kapsülleri listele
   - `GET /api/capsules?status=active` - Duruma göre filtrele
   - `GET /api/capsules/{capsule_serial}` - Detay görüntüle

5. ✅ **Testler**
   - Unit Tests: 4 test (Logic, tarih dönüşümü, hata yönetimi, updateOrCreate)
   - Integration Tests: 5 test (API endpoints + Artisan komutu)
   - **Toplam: 11 test, 32 assertion - %100 başarılı**

6. ✅ **Swagger Belgelendirmesi**
   - URL: `http://localhost:8000/api/documentation`
   - Interactive API testing

7. ✅ **Laravel Passport (OAuth 2.0)**
   - Token-based authentication
   - `auth:api` middleware

---

## 📋 Kurulum Adımları

### 1. Gereksinimler
- PHP 8.2+
- Composer
- MySQL/MariaDB
- Node.js & NPM (opsiyonel)

### 2. Projeyi Klonlama
```bash
git clone <repository-url>
cd spacex-api-sync
```

### 3. Bağımlılıkları Yükleme
```bash
composer install
```

### 4. Environment Yapılandırması
```bash
cp .env.example .env
php artisan key:generate
```

**.env Dosyasını Düzenleyin:**
```env
APP_NAME="SpaceX API Sync"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spacex_api_sync
DB_USERNAME=root
DB_PASSWORD=

# Mail Configuration (Gmail örneği)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"

# Queue Configuration (Notification için)
QUEUE_CONNECTION=database
```

### 5. Veritabanı Kurulumu
```bash
# Veritabanını oluşturun
mysql -u root -p
CREATE DATABASE spacex_api_sync;
exit;

# Migration'ları çalıştırın
php artisan migrate

# Passport kurulumu
php artisan passport:install
```

### 6. Admin Kullanıcı Oluşturma
```bash
php artisan tinker
```

Tinker içinde:
```php
User::factory()->create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => bcrypt('password')
]);
exit
```

### 7. Queue Worker Başlatma (Notification için)
Yeni bir terminal açın:
```bash
php artisan queue:work
```

### 8. Scheduler'ı Başlatma

#### Windows (Local Development):
Yeni bir PowerShell terminali açın:
```powershell
while ($true) { php artisan schedule:run; Start-Sleep -Seconds 60 }
```

#### Linux/macOS (Production):
Cron job ekleyin:
```bash
crontab -e
```

Ekleyin:
```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🎯 Kullanım

### Manuel Senkronizasyon
```bash
php artisan spacex:sync
```

### Otomatik Senkronizasyon
Scheduler çalışıyorsa, her 3 dakikada bir otomatik çalışır.

### Scheduled Komutları Görüntüleme
```bash
php artisan schedule:list
```

Çıktı:
```
*/3 * * * *  php artisan spacex:sync ......... Next Due: 14 seconds from now
```

---

## 🔐 API Token Alma

### Yöntem 1: Personal Access Token (Önerilen)
```bash
php artisan tinker
```

```php
$user = User::where('email', 'admin@example.com')->first();
$token = $user->createToken('My API Token')->accessToken;
echo $token;
```

Token'ı kopyalayın ve kullanın.

### Yöntem 2: OAuth Password Grant
```bash
POST http://localhost:8000/oauth/token
Content-Type: application/json

{
    "grant_type": "password",
    "client_id": "YOUR_CLIENT_ID",
    "client_secret": "YOUR_CLIENT_SECRET",
    "username": "admin@example.com",
    "password": "password"
}
```

---

## 🧪 Testleri Çalıştırma

### Tüm Testler
```bash
php artisan test
```

### Sadece Unit Tests
```bash
php artisan test --testsuite=Unit
```

### Sadece Feature Tests
```bash
php artisan test --testsuite=Feature
```

### Belirli Bir Test Dosyası
```bash
php artisan test --filter=SyncLogicUnitTest
```

---

## 📧 E-posta Bildirimleri

### Gmail Yapılandırması

1. **Gmail App Password Oluşturma:**
   - Google Account → Security → 2-Step Verification → App passwords
   - "Mail" seçin ve bir şifre oluşturun

2. **.env Dosyasını Güncelleyin:**
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=your-email@gmail.com
   MAIL_PASSWORD=generated-app-password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=your-email@gmail.com
   MAIL_FROM_NAME="SpaceX API Sync"
   ```

3. **Test E-postası Gönderme:**
   ```bash
   php artisan tinker
   ```
   
   ```php
   $user = User::first();
   $user->notify(new \App\Notifications\SyncCompletedNotification(10, 'success'));
   ```

### Mailtrap (Test için)

1. [Mailtrap.io](https://mailtrap.io/) hesabı oluşturun
2. Inbox → SMTP Settings → Laravel seçin
3. .env'ye kopyalayın:
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=sandbox.smtp.mailtrap.io
   MAIL_PORT=2525
   MAIL_USERNAME=your-mailtrap-username
   MAIL_PASSWORD=your-mailtrap-password
   MAIL_ENCRYPTION=tls
   ```

---

## 📊 Swagger API Belgeleri

### Erişim
```
http://localhost:8000/api/documentation
```

### Swagger ile Test Etme

1. **Authorize Butonuna Tıklayın**
2. Token'ınızı yapıştırın: `Bearer YOUR_TOKEN`
3. "Authorize" butonuna tıklayın
4. Artık endpoint'leri test edebilirsiniz

### Swagger Belgelerini Güncelleme
```bash
php artisan l5-swagger:generate
```

---

## 🔄 Event ve Listener Akışı

```
Artisan Command (spacex:sync)
    ↓
API'den Veri Çek
    ↓
Veritabanına Kaydet (updateOrCreate)
    ↓
DataSyncCompleted Event Dispatch
    ↓
SendSyncNotification Listener
    ↓
Admin Kullanıcılara E-posta Gönder
```

---

## 📝 Loglar

### Log Dosyası Konumu
```
storage/logs/laravel.log
```

### Canlı Log İzleme
```bash
tail -f storage/logs/laravel.log
```

PowerShell:
```powershell
Get-Content storage/logs/laravel.log -Wait
```

### Log Formatı
```json
{
    "message": "✅ SpaceX Data Sync Completed.",
    "context": {
        "total_items": 20
    },
    "level": "info",
    "datetime": "2025-11-11 12:00:00"
}
```

---

## 🚨 Sorun Giderme

### Problem: Queue worker çalışmıyor
**Çözüm:**
```bash
php artisan queue:restart
php artisan queue:work
```

### Problem: Scheduler çalışmıyor
**Çözüm:**
```bash
# Manuel test
php artisan schedule:run

# Liste kontrol
php artisan schedule:list
```

### Problem: E-posta gönderilmiyor
**Çözüm:**
1. Queue worker çalışıyor mu kontrol edin
2. `.env` dosyasındaki mail ayarlarını kontrol edin
3. Log dosyasını inceleyin:
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Problem: SSL sertifika hatası
**Çözüm:**
Production'da `SyncSpaceXData.php` dosyasında:
```php
// Development
$response = Http::withoutVerifying()->get('...');

// Production
$response = Http::get('...');
```

### Problem: Testler başarısız
**Çözüm:**
```bash
# Cache temizle
php artisan config:clear
php artisan cache:clear

# Testleri tekrar çalıştır
php artisan test
```

---

## 🎉 Production'a Geçiş

### 1. Environment Ayarları
```env
APP_ENV=production
APP_DEBUG=false
```

### 2. Optimize Etme
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

### 3. Queue Worker (Supervisor ile)
```ini
[program:spacex-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker.log
```

### 4. Cron Job
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 📚 Ek Kaynaklar

- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Passport](https://laravel.com/docs/passport)
- [L5-Swagger Documentation](https://github.com/DarkaOnLine/L5-Swagger)
- [SpaceX API](https://github.com/r-spacex/SpaceX-API)

---

## 📞 Destek

Herhangi bir sorun yaşarsanız:
1. `storage/logs/laravel.log` dosyasını kontrol edin
2. `php artisan test` çalıştırın
3. GitHub Issues'da soru sorun

**Proje Başarıyla Tamamlandı! 🎉**
