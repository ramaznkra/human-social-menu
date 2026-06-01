# Human QR Menü — Kurulum Rehberi

**Human** · Social People
Laravel tabanlı QR menü, sipariş, garson/kasa paneli ve TV ekran sistemi.

Bu rehber 3 senaryoya göre ayrılmıştır:

1. **[Yeni PC'de Çalıştırma](#1-yeni-pcde-çalıştırma-sunum--demo)** — temiz bir bilgisayarda sıfırdan ayağa kaldırma (sunum/demo).
2. **[Tasarım / Local Geliştirme](#2-tasarım--local-geliştirme)** — kod/tasarım değişikliği yaparken.
3. **[Canlıya Alma (Production)](#3-canlıya-alma-production)** — gerçek sunucu/hosting kurulumu.

---

## Gereksinimler

| Araç | Sürüm | Not |
|------|-------|-----|
| PHP | **8.3+** | Uzantılar: `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`, `curl`, `zip`, `gd` |
| Composer | Güncel | PHP paket yöneticisi |
| Node.js | LTS (18+) | `npm` ile birlikte gelir |
| Veritabanı | SQLite (varsayılan) veya MySQL 8 | Demo için SQLite yeterli |

> **Windows için en kolay yol:** [Laravel Herd](https://herd.laravel.com) — PHP + Composer + Node'u tek kurulumda getirir.
> `gd` uzantısı kapalıysa QR kodları otomatik SVG üretilir; görsel yükleme/optimizasyon için açık olması önerilir.

### İlk kurulumda bilgisayara kurulacak programlar (unutmamak için)

Temiz bir bilgisayarda **sırasıyla** şunlar kurulur:

| # | Program | Sürüm | İndirme | Not |
|---|---------|-------|---------|-----|
| 1 | **PHP** | **8.3 veya üstü** (8.3 / 8.4) | [windows.php.net/download](https://windows.php.net/download) (TS x64) | Kurulum sonrası `php -v` çalışmalı. |
| 2 | **Composer** | Güncel (2.x) | [getcomposer.org](https://getcomposer.org/download/) | `composer -V` çalışmalı. PHP kurulu olmalı. |
| 3 | **Node.js** | **LTS 18+ (20 önerilir)** | [nodejs.org](https://nodejs.org) | `npm` ile gelir. `node -v` / `npm -v`. |
| 4 | **Git** *(opsiyonel)* | Güncel | [git-scm.com](https://git-scm.com) | Projeyi klonlamak için (USB ile taşıyorsan gerekmez). |

> **Kısa yol (Windows):** Yukarıdaki 1–3 yerine tek başına **[Laravel Herd](https://herd.laravel.com)** kurmak yeterli — PHP + Composer + Node'u birlikte getirir.

**Gerekli PHP uzantıları** (genelde varsayılan açık gelir): `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`, `curl`, `zip`, `gd`.
Açık olup olmadığını görmek için: `php -m` (listede görünmeliler). `gd` yoksa veya WebP çalışmıyorsa → [GD / WebP bölümüne](#gd--webp--görsel-optimizasyonu) bakın.

Kurulum doğrulama (hepsi sürüm yazdırmalı):

```powershell
php -v
composer -V
node -v
npm -v
```

### Paket farkı — Local vs Production

Çalışan (runtime) paketler **iki ortamda da aynıdır**; fark kurulum biçimindedir:

| | Local (geliştirme/demo) | Production (canlı) |
|---|---|---|
| Composer | `composer install` (dev paketleri **dahil**: phpunit, pint, pail, faker…) | `composer install --no-dev --optimize-autoloader` (dev paketleri **hariç**) |
| Asset (npm) | `npm install` + `npm run dev` (canlı/HMR) | `npm run build` (statik çıktı → `public/build/`) |
| Node sunucuda | Gerekli | **Opsiyonel** — derlemeyi yerelde yapıp `public/build/` yüklenebilir |

> Runtime bağımlılıkları aynı: `laravel/framework`, `laravel/reverb`, `pusher/pusher-php-server`, `laravel/tinker`, `barryvdh/laravel-dompdf`, `simplesoftwareio/simple-qrcode` (+ npm: `laravel-echo`, `pusher-js`). Production'da yalnızca `require-dev` paketleri (`phpunit`, `laravel/pint`, `laravel/pail`, `faker`, `collision`, `mockery`) kurulmaz.

### Önemli ekranlar

| Ekran | URL | Açıklama |
|-------|-----|----------|
| QR Menü | `/menu` veya `/menu/{masa-token}` | Müşteri menüsü + sipariş (normal mobil web) |
| Sipariş Durumu | `/siparis/{id}/durum` | Müşteri sipariş takibi |
| TV Ekranı | `/ekran` | Tam ekran görsel slider |
| Mutfak | `/mutfak` | Sipariş takip paneli |
| Kasa / Canlı Siparişler | `/admin/live-orders` | Bekleyen / hazırlanan / tamamlanan + masa çağrıları |
| Admin | `/admin` | Tüm yönetim |
| Garson Paneli | `/waiter/dashboard` | Mobil canlı akış + hızlı sipariş (yalnızca `waiter` rolü, PWA) |
| Geçmiş Adisyonlar | `/admin/orders/archive` | Tamamlanan/iptal kayıtlar, masa/tarih filtresi, PDF |

### Varsayılan giriş bilgileri (seed)

| Rol | Giriş URL | E-posta | Şifre |
|-----|-----------|---------|-------|
| Admin / Kasa | `/admin/giris` | `admin@human.com` | `human2026` |
| Garson | `/admin/giris` → otomatik `/waiter/dashboard` | `garson@human.com` | `human2026` |

> İlk girişten sonra şifreleri mutlaka değiştirin.

---

## 1) Yeni PC'de Çalıştırma (Sunum / Demo)

Hedef: temiz bir laptopta sistemi hızlı ve sorunsuz ayağa kaldırmak.

### Yöntem A — Çalışan klasörü kopyalama (sunum için EN GARANTİLİ)

Salonda internet/derleme sorunu yaşamamak için, **çalışan proje klasörünü olduğu gibi** (USB ile) kopyalayın. `vendor/`, `node_modules/`, `public/build/`, `.env` ve `database/database.sqlite` **dahil** her şey gitsin.

Laptopta tek yapılacak:

```powershell
cd human-qr-menu
php artisan storage:link   # symlink kopyalanmadıysa
composer dev
```

Tarayıcı: http://127.0.0.1:8000

> Sunumdan önce bu işlemi bir kez deneyin: kopyaladıktan sonra `composer dev` ile uçtan uca test edin (sipariş → kasa → garson → hesap kapatma).

### Yöntem B — Sıfırdan kurulum (temiz PC, internet var)

```powershell
cd human-qr-menu
composer install
copy .env.example .env
php artisan key:generate

# SQLite veritabanı dosyasını oluştur (repoda yer almaz)
ni database\database.sqlite

php artisan migrate --seed     # tabloları kurar + demo verisini yükler
php artisan storage:link
npm install
npm run build
```

Çalıştırma (realtime dahil, tek komut):

```powershell
composer dev
```

Tarayıcı: http://127.0.0.1:8000

### Sunum için kritik ipuçları

1. **Realtime için `reverb` + `queue` şart.** `composer dev` ikisini de açar. Kapalıysa bildirimler ~3 sn'lik yedek (polling) ile yine düşer ama anlık olmaz.
2. **Ses:** Tarayıcı autoplay politikası gereği garson/kasa ekranında **bir kez tıklamadan** bildirim sesi çıkmaz. Sunum öncesi her ekrana bir kez tıklayın.
3. **Telefonu garson olarak kullanacaksanız** (aynı Wi-Fi):
   - `php artisan serve --host=0.0.0.0 --port=8000`
   - `.env` içinde `APP_URL` ve `REVERB_HOST`'u laptopun yerel IP'sine ayarlayın (ör. `192.168.1.20`), sonra `npm run build`.
   - Telefondan `http://192.168.1.20:8000` açın.
4. **Temiz demo için veri sıfırlama:** `php artisan migrate:fresh --seed` (DİKKAT: tüm veriyi siler).

---

## 2) Tasarım / Local Geliştirme

Kod, tasarım (CSS/JS) veya menü içeriği üzerinde çalışırken.

### İlk kurulum

```powershell
cd human-qr-menu
composer install
copy .env.example .env
php artisan key:generate
ni database\database.sqlite
php artisan migrate --seed
php artisan storage:link
npm install
```

### Geliştirme sırasında

**Tek komut (önerilen):**

```powershell
composer dev
```

Şunları aynı anda çalıştırır: `php artisan serve` + `reverb:start` + `queue:work` + `npm run dev`.
Tarayıcı: http://127.0.0.1:8000 — değişiklik sonrası gerekirse **Ctrl+F5**.

**Ayrı terminallerde çalıştırmak isterseniz:**

```powershell
php artisan serve
php artisan reverb:start --host=127.0.0.1 --port=8080
php artisan queue:work --tries=1 --timeout=0
npm run dev
```

> `reverb:start` açık ama `queue:work` kapalıysa event'ler kuyruğa düşer, tarayıcıya anlık gitmez.

### Asset derleme notları

- `npm run dev` çalışırken CSS/JS değişiklikleri otomatik yansır (HMR).
- `npm run dev` kullanmıyorsanız her CSS/JS değişikliğinden sonra: `npm run build`.
- Sadece PHP/veritabanı değiştiyse: yeni migration varsa `php artisan migrate`.

### Realtime mimarisi (özet)

- Olaylar `orders` public kanalına yayınlanır (Reverb / Pusher protokolü).
- Eventler: `OrderCreated`, `OrderStatusUpdated`, `TableCallReceived`, `TableCallForwarded`.
- İstemci: `resources/js/echo.js` (Laravel Echo + pusher-js).
- **Mutfak / kasa ekranı** (`/mutfak`, `/admin/live-orders`): WebSocket bağlıyken yeni siparişler event payload'ı ile anında listeye eklenir; yedek polling 30 sn'de bir (Reverb kapalıysa 4 sn).
- **Garson PWA**: aynı `OrderCreated` event'ini dinler; sayfa yenilemeden kart ekler.
- Livewire kullanılmaz — tüm canlı güncellemeler Echo + vanilla JS ile yapılır.

### Sipariş & hesap akışı

1. Müşteri sipariş verir → **kasaya** düşer.
2. Kasa: **Kabul Et · Hazırlanıyor** → **Mutfakta Hazır** → garsona anlık bildirim (zil + titreşim).
3. Garson siparişi masaya götürür → **Teslim Edildi**.
4. Müşteri **Hesap İste** der → bildirim **aynı anda hem kasaya hem garsona** düşer.
5. Hesabı **garson da kasa da** Nakit/Kart ile kapatabilir → tüm ekranlarda anında kapanır.
6. **Garson Çağır** bildirimi doğrudan garsona gider.

> Kasa, hesap çağrısında **➜ Garsona Yönlendir (POS)** ile garsona ekstra POS hatırlatması da gönderebilir.

### Menü / içerik yönetimi (admin)

| Bölüm | İşlev |
|-------|-------|
| Social Spotted | Menü üstündeki fotoğraf carousel |
| Kategoriler | Kapak görseli (yalnızca admin görür) + örnek görseller, aktif/pasif toggle |
| Ürünler | Görsel, fiyat, rozet (hazır chip önerileri), aktif/pasif toggle |
| Masalar & QR | Yeni masa → otomatik QR (PNG/SVG), `/menu/{uuid}` |
| Ayarlar | Günün mottosu + Wi-Fi şifresi (menü banner) |
| Ekran Slaytları | TV ekranı görselleri |

- Çoklu dil: müşteri menüsünde **TR / EN / RU**. Adminde TR zorunlu, EN/RU opsiyonel (boşsa TR gösterilir).
- Garson/manuel sipariş: hem admin hem garson ekranında **➕ Yeni Sipariş Ekle**.
- Ürünlere örnek 1600×1200 görsel: `php artisan products:seed-sample-images` (yeniden: `--force`).
- Mevcut masalar için QR yenileme: `php artisan tables:regenerate-qr`.

### PWA (yalnızca personel)

- Müşteri tarafı (`/menu`) normal mobil web; PWA eklenmez.
- PWA sadece garson/admin layout'larında aktiftir.
- Dosyalar: `public/manifest-waiter.json`, `public/staff-sw.js`, `public/icons/waiter-app-icon.svg`.
- Service worker cache'i nedeniyle tasarım değişikliğini görmek için **Ctrl+F5** gerekebilir.

> **Windows notu:** `composer dev:logs` (`php artisan pail`) `pcntl` gerektirir ve Windows'ta çalışmaz. Log için `storage/logs/laravel.log` dosyasını izleyin.

---

## 3) Canlıya Alma (Production)

### 3.1 Gereksinimler

- PHP 8.3+ (gerekli uzantılarla), Composer
- SQLite veya MySQL 8
- Apache (`mod_rewrite`) veya Nginx
- **Kalıcı süreç yönetimi** (Supervisor / systemd / PM2) — Reverb ve queue worker için
- HTTPS (SSL sertifikası)

### 3.2 Dosyaları yükleyin

Tüm proje klasörünü sunucuya yükleyin (Git / FTP / Dosya Yöneticisi).

**Önemli:** Domain document root'u **`public`** klasörüne yönlendirilmelidir.
- cPanel → Domains → Document Root → `.../human-qr-menu/public`

### 3.3 .env ayarları (production)

```env
APP_NAME=Human
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sizin-domain.com

# SQLite
DB_CONNECTION=sqlite
# veya MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=human_menu
# DB_USERNAME=...
# DB_PASSWORD=...

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
BROADCAST_CONNECTION=reverb
FILESYSTEM_DISK=local

REVERB_APP_ID=human-qr-menu
REVERB_APP_KEY=human-app-key
REVERB_APP_SECRET=human-app-secret
REVERB_HOST=sizin-domain.com
REVERB_PORT=443
REVERB_SCHEME=https

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

> Production'da `REVERB_APP_KEY` / `SECRET` değerlerini mutlaka kendi gizli değerlerinizle değiştirin. WSS (443) için Reverb'i bir reverse proxy (Nginx) arkasına alın.

### 3.4 Kurulum komutları (SSH)

```bash
cd /path/to/human-qr-menu
composer install --no-dev --optimize-autoloader

# SQLite kullanıyorsanız:
touch database/database.sqlite

php artisan key:generate
php artisan migrate --force --seed
php artisan storage:link

# Asset derleme (sunucuda Node varsa)
npm install
npm run build

# İzinler
chmod -R 775 storage bootstrap/cache

# Cache (deploy sonrası her seferinde)
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> Sunucuda Node yoksa: `npm run build`'i yerelde çalıştırıp `public/build/` klasörünü yükleyin.
> SSH yoksa: yerelde kurup `vendor/`, `.env`, `database/database.sqlite`, `public/build/` dosyalarını yükleyin.

### 3.5 Realtime servisleri (sürekli çalışmalı)

Reverb ve queue worker arka planda **kesintisiz** çalışmalıdır. Supervisor örneği:

```ini
[program:human-reverb]
command=php /path/to/human-qr-menu/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
stopwaitsecs=10

[program:human-queue]
command=php /path/to/human-qr-menu/artisan queue:work --tries=1 --timeout=0
autostart=true
autorestart=true
```

> Deploy sonrası bu iki servisi **yeniden başlatın** (kod/değişiklikleri almaları için).

### 3.6 Zamanlanmış görev (otomatik arşiv)

3 saatten eski açık siparişleri saatlik iptal/arşivler (`orders:archive-stale`). Cron:

```bash
* * * * * cd /path/to/human-qr-menu && php artisan schedule:run >> /dev/null 2>&1
```

Manuel test: `php artisan orders:archive-stale`

### 3.7 .htaccess (alt dizinde çalışıyorsa)

```apache
RewriteBase /human-qr-menu/public/
```

---

## QR Kod Oluşturma

1. Admin → **Masalar & QR**
2. Her masanın linkini kopyalayın (ör. `https://domain.com/menu/abc123token`)
3. QR üretip masaya yapıştırın. Genel menü için: `https://domain.com/menu`

## TV Ekranı

1. Smart TV / mini PC tarayıcısında tam ekran (F11): `https://domain.com/ekran`
2. Admin → **Ekran Slaytları**'ndan görsel yükleyin (1920×1080 önerilir).

---

## Sorun Giderme

| Belirti | Çözüm |
|---------|-------|
| Bildirimler anlık gelmiyor | `reverb:start` + `queue:work` çalışıyor mu? `composer dev` kullanın. |
| Ses çıkmıyor | Ekrana bir kez tıklayın (tarayıcı autoplay engeli). |
| Görseller görünmüyor | `php artisan storage:link` çalıştırın. |
| Tasarım değişikliği yansımıyor | `npm run build` + tarayıcıda **Ctrl+F5** (service worker cache). |
| `no such table` hatası | `database/database.sqlite` oluşturup `php artisan migrate --seed`. |
| QR PNG üretilmiyor | PHP `ext-gd` kapalı; SVG otomatik üretilir, sorun değil. |
| Ürün/kategori görselleri WebP'ye dönüşmüyor | PHP `ext-gd` kapalı veya WebP desteği yok — aşağıdaki **GD / WebP** bölümüne bakın. |
| Windows `composer dev:logs` hatası | `pcntl` Windows'ta yok; `storage/logs/laravel.log` izleyin. |

### GD / WebP — görsel optimizasyonu

Admin panelinden yüklenen ürün, kategori ve slayt görselleri **maks. 800px genişliğe küçültülüp `.webp` formatında** kaydedilir. Bunun için PHP'de **GD** uzantısının açık olması gerekir.

**Kontrol:**

```powershell
php -m | findstr /I gd
php --ri gd
```

Çıktıda `gd` görünmeli; `WebP Support => enabled` satırı varsa WebP dönüşümü de çalışır.

**Windows'ta GD'yi açma:**

1. `php.ini` yolunu bulun: `php --ini` → `Loaded Configuration File` satırı (ör. `C:\php\php.ini`).
2. Dosyada `;extension=gd` satırını bulun; başındaki `;` kaldırın:
   ```ini
   extension=gd
   ```
3. `C:\php\ext\php_gd.dll` dosyasının var olduğundan emin olun.
4. Web sunucusu kullanıyorsanız (Apache/Nginx/IIS) yeniden başlatın; yalnızca CLI ise yeni terminal açmanız yeterli.
5. Tekrar doğrulayın: `php --ri gd` → `GD Support => enabled`.

**Linux / production sunucu (Debian/Ubuntu örneği):**

```bash
sudo apt install php8.3-gd
sudo systemctl restart php8.3-fpm   # veya apache2 / nginx
php --ri gd
```

**GD kapalıysa ne olur?** Görseller orijinal boyut ve formatta kaydedilir (sistem çalışmaya devam eder). QR kodları için GD yoksa PNG yerine SVG üretilir.

**WebP testi (isteğe bağlı):** Admin → Ürünler'den büyük bir JPG/PNG yükleyin; `storage/app/public/products/` altında `.webp` dosyası oluşmalıdır.

---

## Klasör Yapısı

```
human-qr-menu/
├── app/              # Uygulama kodu (Controller, Model, Event, Middleware)
├── database/         # Migration, seeder, sqlite dosyası
├── public/           # Web kökü (index.php, build/, manifest, service worker)
├── resources/        # Blade şablonları, CSS, JS
├── routes/           # web.php, channels.php
├── storage/          # Yüklenen görseller, loglar
├── KURULUM.md        # Bu dosya
└── .env              # Ortam ayarları
```

---

## Güvenlik Kontrol Listesi (production)

1. `APP_DEBUG=false`
2. Admin ve garson şifrelerini değiştirin
3. `REVERB_APP_KEY` / `SECRET` değerlerini değiştirin
4. `storage` ve `.env` web'den erişilemez olmalı (`public` dışında)
5. HTTPS kullanın

İyi çalışmalar — **Human · Social People**
