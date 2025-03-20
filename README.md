# Laravel Diyetisyen API Uygulaması

Bu proje, diyetisyenler ve müşterileri arasındaki ilişkiyi yönetmek için Laravel tabanlı bir API sunucusudur. Flutter ile geliştirilecek mobil uygulamanın backend tarafıdır.

## Proje Hakkında

Bu uygulama, diyetisyenlerin müşterileriyle etkileşime geçmesini, diyet planları oluşturmasını, beslenme takibi yapmasını ve ilerleme durumlarını izlemesini sağlar. Ayrıca abonelik, ödeme sistemi ve tarif paylaşımı gibi özellikleri de içerir.

## Teknolojiler

- PHP 
- Laravel 
- MySQL 
- Laravel Sanctum (API Kimlik Doğrulama)
- Laravel Eloquent ORM
- Soft Delete 
- JWT (JSON Web Token)

## Kurulum

1. Projeyi klonlayın:
   ```bash
   git clone https://github.com/kullaniciadi/laravel-dietitian-app.git
   cd laravel-dietitian-app
   ```

2. Bağımlılıkları yükleyin:
   ```bash
   composer install
   ```

3. Çevre değişkenlerini ayarlayın:
   ```bash
   cp .env.example .env
   ```

4. `.env` dosyasını düzenleyerek veritabanı bağlantı bilgilerinizi girin.

5. Uygulama anahtarını oluşturun:
   ```bash
   php artisan key:generate
   ```

6. Veritabanı tablolarını oluşturun:
   ```bash
   php artisan migrate
   ```

7. (İsteğe bağlı) Örnek verileri yükleyin:
   ```bash
   php artisan db:seed
   ```

8. Geliştirme sunucusunu başlatın:
   ```bash
   php artisan serve
   ```

## Veri Modeli

Uygulama aşağıdaki ana veri modellerini kullanır:

### Kullanıcı ve Profil Tabloları
- **users**: Tüm kullanıcılar (diyetisyenler ve müşteriler)
- **dietitians**: Diyetisyenlere özel bilgiler
- **clients**: Müşterilere özel bilgiler

### Diyet ve Beslenme Tabloları
- **diet_plans**: Müşterilere özel diyet planları
- **diet_plan_meals**: Diyet planlarındaki öğünler
- **foods**: Besin veritabanı
- **food_logs**: Müşterilerin beslenme günlükleri

### Abonelik ve Ödeme Tabloları
- **subscription_plans**: Diyetisyenlerin sunduğu abonelik planları
- **subscriptions**: Müşterilerin aktif abonelikleri
- **payments**: Yapılan ödemeler

### İlerleme ve Hedef Tabloları
- **progress**: Müşterilerin ilerleme kayıtları
- **goals**: Müşterilerin hedefleri

### İletişim ve İçerik Tabloları
- **messages**: Kullanıcılar arası mesajlaşma
- **recipes**: Diyetisyenler tarafından paylaşılan tarifler



## Kimlik Doğrulama ve Yetkilendirme

Uygulama, Laravel Sanctum kullanarak token tabanlı kimlik doğrulama sağlar. Her API isteği için geçerli bir token gereklidir.

```php
// Örnek istek:
$response = $http->withHeaders([
    'Authorization' => 'Bearer ' . $token,
    'Accept' => 'application/json',
])->get('/api/user');
```

Yetkilendirme, kullanıcı rollerine göre yapılır:

- `dietitian`: Diyetisyen rolüne sahip kullanıcılar
- `client`: Müşteri rolüne sahip kullanıcılar
-  `admin`: Gerekli admin işlemini sağlayan kullanıcı


## Veritabanı İlişkileri

Ana veritabanı ilişkileri şunlardır:

- Bir kullanıcı (`users`) ya bir diyetisyen (`dietitians`) ya da bir müşteri (`clients`) olabilir.
- Bir diyetisyen (`dietitians`) birçok müşteriye (`clients`) sahip olabilir.
- Bir müşteri (`clients`) en fazla bir diyetisyene (`dietitians`) sahip olabilir.
- Bir diyetisyen (`dietitians`) birçok diyet planı (`diet_plans`) oluşturabilir.
- Bir diyet planı (`diet_plans`) birçok öğün (`diet_plan_meals`) içerebilir.
- Bir müşteri (`clients`) birçok beslenme günlüğü (`food_logs`) kaydı tutabilir.
- Bir diyetisyen (`dietitians`) birçok abonelik planı (`subscription_plans`) oluşturabilir.




## Hata Kodları

API, şu hata kodlarını döndürebilir:

- 200: Başarılı
- 201: Başarıyla oluşturuldu
- 400: Geçersiz istek
- 401: Yetkisiz erişim
- 403: Yasaklanmış erişim
- 404: Kaynak bulunamadı
- 422: Doğrulama hatası
- 500: Sunucu hatası


