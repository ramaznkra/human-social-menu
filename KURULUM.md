# Human QR Menü — Kurulum Rehberi

**Human** · Social People  
Laravel tabanlı QR menü, sipariş ve TV ekran sistemi.

---

## Özellikler

| Sayfa | URL | Açıklama |
|-------|-----|----------|
| QR Menü | `/menu` veya `/menu/{masa-token}` | Social Spotted + kategoriler + sipariş |
| Sipariş Durumu | `/siparis/{id}/durum` | Müşteri takip |
| TV Ekranı | `/ekran` | 10 sn geçişli tam ekran slider |
| Mutfak | `/mutfak` | Sipariş takip paneli |
| Admin | `/admin` | Tüm yönetim |
| Garson paneli | `/waiter/dashboard` | Mobil canlı akış + hızlı sipariş (yalnızca `waiter` rolü) |
| Canlı Siparişler | `/admin/live-orders` | Beklemede / hazırlanıyor / masada |
| Geçmiş Adisyonlar | `/admin/orders/archive` | Tamamlanan ve iptal kayıtlar |

---

## Yerel Kurulum (Geliştirme)

```bash
cd human-qr-menu
composer install
cp .env.example .env   # Windows: copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Tarayıcıda: http://127.0.0.1:8000

**Her turda yapılan değişiklikleri localde görmek için** (iki terminal açık kalsın): `php artisan serve` · `npm run dev` — ardından tarayıcıda http://127.0.0.1:8000 ve gerekirse **Ctrl+F5**. Sadece PHP/veritabanı değiştiyse: `php artisan migrate` (yeni migration varsa). `npm run dev` kullanmıyorsanız her CSS/JS değişikliğinden sonra bir kez: `npm run build`.

### Realtime (Laravel Reverb)

Canlı siparişlerin garson ekranına sayfa yenilemeden düşmesi için Reverb + queue worker birlikte çalışmalıdır.

1. Gerekli paketler:

```bash
composer require laravel/reverb pusher/pusher-php-server --ignore-platform-req=ext-gd
npm install laravel-echo pusher-js
```

2. `.env` ayarları:

```env
BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=database

REVERB_APP_ID=human-qr-menu
REVERB_APP_KEY=human-app-key
REVERB_APP_SECRET=human-app-secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
```

3. Tek komutla tüm geliştirme süreçlerini başlatın:

```bash
composer dev
```

Bu komut şunları aynı anda çalıştırır: `serve` + `reverb:start` + `queue:work` + `npm run dev`.

> Not (Windows): `php artisan pail` için `pcntl` gerekir ve Windows'ta desteklenmez. Log takibi için ayrı terminalde `storage/logs/laravel.log` izleyin veya Linux sunucuda `composer dev:logs` kullanın.

4. İsterseniz manuel (ayrı terminaller) çalıştırma:

```bash
php artisan serve
php artisan reverb:start
php artisan queue:work
npm run dev
```

> Not: `reverb:start` açık ama `queue:work` kapalıysa event yayın kuyruğa düşer fakat tarayıcıya anlık gitmez.

### PWA (yalnızca Garson / Admin)

- Müşteri tarafı (`/menu`) normal mobil web olarak kalır; PWA manifest / service worker eklenmez.
- PWA sadece personel ekranlarında aktiftir:
  - `resources/views/layouts/waiter.blade.php`
  - `resources/views/layouts/admin.blade.php`
- Kullanılan dosyalar:
  - `public/manifest-waiter.json`
  - `public/staff-sw.js`
  - `public/icons/waiter-app-icon.svg`

Start URL: `/admin/giris` (giriş sonrası role gore garson/admin ekrana yonlenir).

### Production notu (canlıya alırken)

- Reverb ve queue worker process tabanlıdır; servis olarak sürekli ayakta tutulmalıdır (Supervisor/systemd/PM2 benzeri).
- Realtime için canlıda en az bu iki süreç sürekli çalışmalıdır:
  - `php artisan reverb:start --host=0.0.0.0 --port=8080`
  - `php artisan queue:work --tries=1 --timeout=0`
- Deploy sonrası: `php artisan optimize:clear && php artisan config:cache` çalıştırıp worker/reverb servislerini yeniden başlatın.

### Varsayılan Admin

- **URL:** http://127.0.0.1:8000/admin/giris
- **E-posta:** `admin@human.com`
- **Şifre:** `human2026`
- **Garson:** `garson@human.com` / `human2026` → otomatik `/waiter/dashboard` (admin paneline erişemez)

> İlk girişten sonra şifreyi mutlaka değiştirin.

### Menü görselleri

| Admin bölümü | Ne işe yarar |
|--------------|--------------|
| **Social Spotted** | QR menü üstündeki fotoğraf carousel (admin: Social Spotted) |
| **Kategoriler → Kapak görseli** | PIER tarzı büyük kategori kartları |
| **Masalar & QR** | Yeni masa → otomatik QR (PNG/SVG), link: `/menu?masa=15` |
| **Ayarlar** | Günün mottosu + Wi-Fi şifresi (menü banner) |
| **Bar Ekranı** | Dokunmatik içecek hazırlık paneli |

Örnek dosyalar: `public/images/menu/` — istediğiniz zaman panelden değiştirin.

Ürünlere örnek yüksek çözünürlüklü fotoğraf (1600×1200): `php artisan products:seed-sample-images`  
(Yeniden yüklemek için: `--force`)

Mevcut masalar için QR yenileme: `php artisan tables:regenerate-qr`

> PNG için PHP `ext-gd` gerekir; yoksa otomatik SVG üretilir.

---

## Web Hosting Kurulumu (cPanel / Plesk)

### Gereksinimler

- PHP 8.2+
- SQLite veya MySQL
- `mod_rewrite` (Apache)
- Composer (SSH varsa) veya yerelde build edip yükleme

### Adım 1 — Dosyaları yükleyin

Tüm proje klasörünü sunucuya yükleyin (FTP / Dosya Yöneticisi).

### Adım 2 — Document Root

**Önemli:** Domain kökünü `public` klasörüne yönlendirin.

- cPanel → Domains → Document Root → `public_html/human-qr-menu/public`
- Veya `public` içeriğini `public_html` köküne kopyalayın ve `index.php` yollarını düzenleyin

`public/index.php` içindeki yollar zaten bir üst dizini işaret eder.

### Adım 3 — .env ayarları

```env
APP_NAME=Human
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sizin-domain.com

DB_CONNECTION=sqlite
# veya MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_DATABASE=human_menu
# DB_USERNAME=...
# DB_PASSWORD=...

SESSION_DRIVER=file
CACHE_STORE=file
BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=database

REVERB_APP_ID=human-qr-menu
REVERB_APP_KEY=human-app-key
REVERB_APP_SECRET=human-app-secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=https
```

MySQL kullanıyorsanız veritabanını panelden oluşturup `php artisan migrate --seed` çalıştırın.

### Adım 4 — SSH komutları (varsa)

```bash
cd /home/kullanici/human-qr-menu
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force --seed
php artisan storage:link
chmod -R 775 storage bootstrap/cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

SSH yoksa: yerelde aynı komutları çalıştırıp `vendor`, `.env`, `database/database.sqlite` dosyalarını yükleyin.

### Adım 5 — storage izinleri

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

Görseller `storage/app/public` altına kaydedilir; `public/storage` symlink gerekir.

### Adım 6 — .htaccess

`public/.htaccess` Laravel ile gelir. Alt dizinde çalışıyorsanız:

```apache
RewriteBase /human-qr-menu/public/
```

---

## QR Kod Oluşturma

1. Admin → **Masalar & QR**
2. Her masanın linkini kopyalayın (ör. `https://domain.com/menu/abc123token`)
3. [qr-code-generator.com](https://www.qr-code-generator.com) veya benzeri ile QR oluşturup masaya yapıştırın
4. Genel menü için: `https://domain.com/menu`

---

## TV Ekranı Kurulumu

1. Smart TV veya mini PC’ye tarayıcı açın
2. Tam ekran (F11): `https://domain.com/ekran`
3. Admin → **Ekran Slaytları**’ndan görselleri yükleyin (1920×1080 önerilir)
4. Her slayt için geçiş süresi (varsayılan 10 sn) ayarlanır

---

## Mutfak / Sipariş Takibi

- Mutfak tableti: `https://domain.com/mutfak` (otomatik yenileme 15 sn)
- Admin sipariş listesi: `/admin/orders`
- Geçmiş adisyon arşivi: `/admin/orders/archive` (sayfalı liste, arama + tarih filtresi, net ciro özeti)
- Arşiv PDF: **Günlük PDF** (seçili/tekil gün) · **Özet & Liste PDF** (filtreye göre net ciro + nakit/kart + adisyon tablosu). Paket: `barryvdh/laravel-dompdf`
- Admin silme onayları tarayıcı `confirm` yerine canlı sipariş bildirimi stilinde toast/onay kartı kullanır
- Canlı ekranda yalnızca **beklemede / hazırlanıyor / masada** siparişler görünür; **tamamlandı** veya **iptal** olanlar anında kaybolur

### Otomatik arşiv (sunucu)

3 saatten eski açık siparişleri saatlik iptal eder (`orders:archive-stale`). Production’da cron:

```bash
* * * * * cd /path/to/human-qr-menu && php artisan schedule:run >> /dev/null 2>&1
```

Manuel test: `php artisan orders:archive-stale`

Canlı panel (Kasa) akışı: yeni sipariş bildirimi → **Kabul Et · Hazırlanıyor** → **Afiyet Olsun** (masaya gitti) → **Nakit/Kart · Kapat** (sipariş kapanır, canlı listeden düşer). Müşteri ekranı: Sipariş Alındı → Hazırlanıyor → Afiyet Olsun; ardından menüye döner, ödemeyi **Hesap İste** ile kendisi talep eder.

**Menü dilleri:** Müşteri menüsünde sağ üstten **TR / EN / RU** seçilir. Ürün ve kategori adları adminde TR (zorunlu) + EN/RU (isteğe bağlı) girilir; boşsa Türkçe gösterilir.

**Garson / manuel sipariş:** Admin panelinde sağ üstte **➕ Yeni Sipariş Ekle** → masa seç → ürün ara/ekle → **Siparişi Onayla**. Canlı ekranda **🤵 Garson Siparişi** rozeti görünür; kartta yalnızca **Nakit** ve **Kart** (Hazırlanıyor / Afiyet Olsun yok). Panelde **Sipariş Kaynağı** kartında QR / garson yüzdesi raporlanır.

---

## Klasör Yapısı

```
human-qr-menu/
├── app/              # Uygulama kodu
├── database/         # Migration & seed
├── public/           # Web kökü (css, index.php)
├── resources/views/  # Arayüz şablonları
├── storage/          # Yüklenen görseller
├── KURULUM.md        # Bu dosya
└── .env              # Ortam ayarları
```

---

## Güvenlik

1. `APP_DEBUG=false` production’da
2. Admin şifresini değiştirin
3. `storage` ve `.env` web’den erişilemez olmalı (public dışında)
4. HTTPS kullanın

---

## Destek & Özelleştirme

- Renkler: `public/css/human.css` (müşteri), `public/css/admin.css` (admin)
- Örnek menü verileri: `database/seeders/HumanSeeder.php`

İyi çalışmalar — **Human · Social People**
