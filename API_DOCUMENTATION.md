# 🚀 SpaceX Capsule API - Kullanım Kılavuzu

## 📋 İçindekiler
- [API Belgeleri](#api-belgeleri)
- [Kimlik Doğrulama](#kimlik-doğrulama)
- [Endpoints](#endpoints)
- [Örnekler](#örnekler)

## 📚 API Belgeleri

Swagger UI üzerinden interaktif API belgelerine erişebilirsiniz:

```
http://localhost:8000/api/documentation
```

veya Laragon kullanıyorsanız:

```
http://spacex-api-sync.test/api/documentation
```

## 🔐 Kimlik Doğrulama

Bu API, **Laravel Passport (OAuth 2.0)** ile korunmaktadır. API'ye erişmek için geçerli bir **Bearer Token** gereklidir.

### Token Alma

#### Yöntem 1: Personal Access Token (Test için önerilir)

```php
use App\Models\User;

$user = User::find(1); // veya User::factory()->create();
$token = $user->createToken('API Token')->accessToken;
```

#### Yöntem 2: OAuth Password Grant

```bash
POST /oauth/token
Content-Type: application/json

{
    "grant_type": "password",
    "client_id": "YOUR_CLIENT_ID",
    "client_secret": "YOUR_CLIENT_SECRET",
    "username": "user@example.com",
    "password": "password",
    "scope": ""
}
```

## 🛣️ Endpoints

### 1. Kapsül Listesi

**GET** `/api/capsules`

Tüm kapsülleri sayfalı olarak listeler.

**Query Parametreleri:**
- `status` (opsiyonel): `active`, `retired`, `destroyed`, `unknown`
- `page` (opsiyonel): Sayfa numarası

**Örnek İstek:**
```bash
curl -X GET "http://localhost:8000/api/capsules?status=active&page=1" \
     -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
     -H "Accept: application/json"
```

**Örnek Yanıt:**
```json
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "capsule_serial": "C101",
            "capsule_id": "dragon1",
            "status": "retired",
            "original_launch": "2010-12-08 15:43:00",
            "missions_count": 1,
            "details": "Reentered after three weeks in orbit",
            "raw_data": {...},
            "created_at": "2025-11-11T08:12:56.000000Z",
            "updated_at": "2025-11-11T08:12:56.000000Z"
        }
    ],
    "per_page": 15,
    "total": 100
}
```

### 2. Kapsül Detayı

**GET** `/api/capsules/{capsule_serial}`

Belirtilen seri numarasına sahip kapsülün detaylarını getirir.

**Path Parametreleri:**
- `capsule_serial`: Kapsül seri numarası (örn: C101)

**Örnek İstek:**
```bash
curl -X GET "http://localhost:8000/api/capsules/C101" \
     -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
     -H "Accept: application/json"
```

**Örnek Yanıt (200 OK):**
```json
{
    "id": 1,
    "capsule_serial": "C101",
    "capsule_id": "dragon1",
    "status": "retired",
    "original_launch": "2010-12-08 15:43:00",
    "missions_count": 1,
    "details": "Reentered after three weeks in orbit",
    "raw_data": {...}
}
```

**Örnek Yanıt (404 Not Found):**
```json
{
    "message": "Capsule not found"
}
```

## 🔧 Artisan Komutları

### Veri Senkronizasyonu

SpaceX API'den veri çekmek için:

```bash
php artisan spacex:sync
```

Bu komut:
- SpaceX API'ye bağlanır
- Tüm kapsül verilerini çeker
- Veritabanına kaydeder (updateOrCreate ile)
- Log kaydı oluşturur

### Swagger Belgelerini Güncelleme

API değişiklikleri yaptıktan sonra Swagger belgelerini yeniden oluşturmak için:

```bash
php artisan l5-swagger:generate
```

## 🧪 Test Etme

### PHPUnit Testleri

```bash
php artisan test --filter=SpaceXIntegrationTest
```

### Postman/Insomnia ile Test

1. Token alın (yukarıdaki yöntemlerden biriyle)
2. Authorization header'ına ekleyin: `Bearer YOUR_TOKEN`
3. İsteklerinizi gönderin

### cURL Örnekleri

**Tüm kapsülleri listele:**
```bash
curl -X GET "http://localhost:8000/api/capsules" \
     -H "Authorization: Bearer YOUR_TOKEN"
```

**Sadece aktif kapsülleri listele:**
```bash
curl -X GET "http://localhost:8000/api/capsules?status=active" \
     -H "Authorization: Bearer YOUR_TOKEN"
```

**Belirli bir kapsülün detayını getir:**
```bash
curl -X GET "http://localhost:8000/api/capsules/C101" \
     -H "Authorization: Bearer YOUR_TOKEN"
```

## 📊 Durum Kodları

| Kod | Açıklama |
|-----|----------|
| 200 | Başarılı istek |
| 401 | Yetkisiz erişim (Token geçersiz veya eksik) |
| 404 | Kaynak bulunamadı |
| 500 | Sunucu hatası |

## 🔍 Loglama

Senkronizasyon işlemleri otomatik olarak loglanır:

```bash
# Log dosyasını görüntüle
cat storage/logs/laravel.log

# veya tail ile canlı takip
tail -f storage/logs/laravel.log
```

## 🏗️ Proje Yapısı

```
spacex-api-sync/
├── app/
│   ├── Console/Commands/
│   │   └── SyncSpaceXData.php      # Senkronizasyon komutu
│   ├── Http/Controllers/Api/
│   │   └── CapsuleController.php   # API Controller (Swagger annotasyonlu)
│   └── Models/
│       ├── Capsule.php              # Kapsül modeli
│       └── User.php                 # Kullanıcı modeli (HasApiTokens)
├── config/
│   ├── auth.php                     # API guard: passport
│   └── l5-swagger.php               # Swagger konfigürasyonu
├── database/
│   ├── factories/
│   │   └── CapsuleFactory.php      # Test factory
│   └── migrations/
│       └── 2025_11_11_*_create_capsules_table.php
├── routes/
│   └── api.php                      # API rotaları (auth:api middleware)
└── tests/
    └── Feature/
        └── SpaceXIntegrationTest.php # Integration testleri
```

## 🚦 Başlangıç Adımları

1. **Gerekli paketleri yükleyin:**
   ```bash
   composer install
   ```

2. **Veritabanını oluşturun:**
   ```bash
   php artisan migrate
   ```

3. **İlk kullanıcıyı oluşturun:**
   ```bash
   php artisan tinker
   >>> User::factory()->create(['email' => 'admin@example.com'])
   ```

4. **Token oluşturun:**
   ```bash
   >>> $user = User::first()
   >>> $token = $user->createToken('My Token')->accessToken
   >>> echo $token
   ```

5. **Veri senkronizasyonu yapın:**
   ```bash
   php artisan spacex:sync
   ```

6. **API'yi test edin:**
   - Swagger UI: http://localhost:8000/api/documentation
   - Postman/Insomnia ile yukarıdaki token'ı kullanın

## 🎯 Özellikler

✅ SpaceX API entegrasyonu
✅ OAuth 2.0 kimlik doğrulama (Passport)
✅ RESTful API endpoints
✅ Swagger/OpenAPI belgelendirmesi
✅ Otomatik veri senkronizasyonu
✅ Filtreleme ve pagination
✅ Kapsamlı test coverage
✅ Loglama sistemi

## 📝 Notlar

- Token'lar varsayılan olarak 1 yıl geçerlidir
- API rate limiting uygulanmamıştır (gerekirse eklenebilir)
- CORS ayarları gerekirse `config/cors.php` üzerinden yapılabilir
- SSL sertifika sorunları için `Http::withoutVerifying()` kullanılmıştır (production'da kaldırılmalı)

## 🆘 Sorun Giderme

**Token çalışmıyor:**
```bash
php artisan passport:install --force
php artisan config:clear
php artisan cache:clear
```

**Swagger belgeleri görünmüyor:**
```bash
php artisan l5-swagger:generate
php artisan route:clear
```

**SSL hatası:**
- `SyncSpaceXData.php` dosyasında `Http::withoutVerifying()` kullanılıyor
- Production için düzgün SSL sertifikası yapılandırın
