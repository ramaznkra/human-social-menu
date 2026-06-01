<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CafeGallery;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionGroup;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\Table;
use App\Models\User;
use App\Support\CurrentRestaurant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class HumanSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::updateOrCreate(
            ['slug' => 'human'],
            [
                'name' => 'Human',
                'kitchen_token' => env('KITCHEN_KIOSK_TOKEN', Str::random(48)),
                'is_active' => true,
            ],
        );

        CurrentRestaurant::run($restaurant, function () use ($restaurant) {
            User::updateOrCreate(
                ['email' => 'admin@human.com'],
                [
                    'name' => 'Human Admin',
                    'password' => Hash::make('human2026'),
                    'role' => User::ROLE_ADMIN,
                    'restaurant_id' => $restaurant->id,
                ]
            );

            User::updateOrCreate(
                ['email' => 'garson@human.com'],
                [
                    'name' => 'Garson',
                    'password' => Hash::make('human2026'),
                    'role' => User::ROLE_WAITER,
                    'restaurant_id' => $restaurant->id,
                ]
            );

            $defaults = [
                'venue_name' => 'Human',
                'venue_slogan' => 'Human Social People',
                'brand_mark' => 'Human',
                'venue_tagline' => 'Human Social People',
                'venue_phone' => '+90 555 000 00 00',
                'venue_address' => 'İstanbul',
                'currency' => '₺',
                'order_enabled' => '1',
                'display_interval' => '10',
                'daily_motto' => 'İyi insanlar, iyi sohbetler.',
                'wifi_password' => 'HumanSocial2026',
                'show_motto_banner' => '1',
                'show_wifi_banner' => '1',
                'spotify_url' => 'https://open.spotify.com/playlist/37i9dQZF1DX0XUsuxWHRQd',
                'spotify_title' => 'HSP Vibes',
                'instagram_url' => 'https://www.instagram.com/ramaznkra/',
                'instagram_handle' => '@ramaznkra',
            ];
            foreach ($defaults as $key => $value) {
                Setting::set($key, $value);
            }

            $categories = [
                ['name' => ['tr' => 'Yiyecek', 'en' => 'Food', 'ru' => 'Еда'], 'slug' => 'yiyecek', 'image' => 'images/categories/samples/yiyecek.svg', 'sort_order' => 1],
                ['name' => ['tr' => 'İçecek', 'en' => 'Drinks', 'ru' => 'Напитки'], 'slug' => 'icecek', 'image' => 'images/categories/samples/icecek.svg', 'sort_order' => 2],
                ['name' => ['tr' => 'Nargile', 'en' => 'Shisha', 'ru' => 'Кальян'], 'slug' => 'nargile', 'image' => 'images/categories/samples/nargile.svg', 'sort_order' => 3],
                ['name' => ['tr' => 'Okey', 'en' => 'Okey', 'ru' => 'Окей'], 'slug' => 'okey', 'image' => 'images/categories/samples/okey.svg', 'sort_order' => 4],
            ];

            foreach ($categories as $cat) {
                Category::updateOrCreate(
                    ['restaurant_id' => $restaurant->id, 'slug' => $cat['slug']],
                    $cat + ['is_active' => true, 'icon' => null],
                );
            }

            $spottedCards = [
                [
                    'image_path' => 'images/menu/slider/misafir-1.jpg',
                    'title' => 'Human Ailesi',
                    'description' => 'Sevgili Cem Yılmaz, imza kahvemizi deneyimlerken… #SocialMoments',
                    'badge_text' => 'Spotted at HSP ✨',
                    'sort_order' => 1,
                ],
                [
                    'image_path' => 'images/menu/slider/mekan-1.jpg',
                    'title' => 'Lounge Atmosferi',
                    'description' => 'Sosyal sohbetlerin ve iyi insanların buluşma noktası.',
                    'badge_text' => 'HSP Moments',
                    'sort_order' => 2,
                ],
                [
                    'image_path' => 'images/menu/slider/misafir-2.jpg',
                    'title' => null,
                    'description' => 'Bugün telefonları bir kenara bırakıp masadakiyle konuşanlara selam olsun.',
                    'badge_text' => 'Spotted at HSP ✨',
                    'sort_order' => 3,
                ],
            ];

            foreach ($spottedCards as $card) {
                CafeGallery::updateOrCreate(
                    ['restaurant_id' => $restaurant->id, 'image_path' => $card['image_path']],
                    $card + ['is_active' => true],
                );
            }

            $products = [
                ['category' => 'yiyecek', 'name' => ['tr' => 'Human Burger', 'en' => 'Human Burger', 'ru' => 'Бургер Human'], 'description' => ['tr' => 'Özel soslu, cheddar peynirli burger', 'en' => 'Signature sauce and cheddar burger', 'ru' => 'Фирменный соус и сыр чеддер'], 'price' => 320, 'badge' => 'Popüler'],
                ['category' => 'yiyecek', 'name' => ['tr' => 'Sosyal Tabağı', 'en' => 'Social Platter', 'ru' => 'Социальная тарелка'], 'description' => ['tr' => 'Paylaşımlık atıştırmalık tabağı', 'en' => 'Sharing snack platter', 'ru' => 'Закуски для компании'], 'price' => 280],
                ['category' => 'yiyecek', 'name' => ['tr' => 'Nachos', 'en' => 'Nachos', 'ru' => 'Начос'], 'description' => ['tr' => 'Guacamole ve salsa ile', 'en' => 'With guacamole and salsa', 'ru' => 'С гуакамоле и сальсой'], 'price' => 195],
                ['category' => 'icecek', 'name' => ['tr' => 'Espresso', 'en' => 'Espresso', 'ru' => 'Эспрессо'], 'description' => ['tr' => 'Tek shot', 'en' => 'Single shot', 'ru' => 'Один шот'], 'price' => 75],
                ['category' => 'icecek', 'name' => ['tr' => 'Latte', 'en' => 'Latte', 'ru' => 'Латте'], 'description' => ['tr' => 'Sütlü kahve', 'en' => 'Milky coffee', 'ru' => 'Кофе с молоком'], 'price' => 95],
                ['category' => 'icecek', 'name' => ['tr' => 'Limonada', 'en' => 'Lemonade', 'ru' => 'Лимонад'], 'description' => ['tr' => 'Taze sıkılmış', 'en' => 'Freshly squeezed', 'ru' => 'Свежевыжатый'], 'price' => 85],
                ['category' => 'icecek', 'name' => ['tr' => 'Mojito', 'en' => 'Mojito', 'ru' => 'Мохито'], 'description' => ['tr' => 'Alkolsüz ferah içecek', 'en' => 'Non-alcoholic refreshment', 'ru' => 'Безалкогольный освежающий напиток'], 'price' => 120, 'badge' => 'Yeni'],
                ['category' => 'nargile', 'name' => ['tr' => 'Elma Nargile', 'en' => 'Apple Shisha', 'ru' => 'Кальян яблоко'], 'description' => ['tr' => 'Klasik elma aroması', 'en' => 'Classic apple flavor', 'ru' => 'Классический яблочный вкус'], 'price' => 450],
                ['category' => 'nargile', 'name' => ['tr' => 'Üzüm Nargile', 'en' => 'Grape Shisha', 'ru' => 'Кальян виноград'], 'description' => ['tr' => 'Yoğun üzüm aroması', 'en' => 'Rich grape flavor', 'ru' => 'Насыщенный виноградный вкус'], 'price' => 450],
                ['category' => 'nargile', 'name' => ['tr' => 'Karışık Nargile', 'en' => 'Mixed Shisha', 'ru' => 'Кальян микс'], 'description' => ['tr' => 'İki aroma seçeneği', 'en' => 'Two flavor options', 'ru' => 'Два варианта вкуса'], 'price' => 500],
                ['category' => 'okey', 'name' => ['tr' => 'Okey Masası (Saatlik)', 'en' => 'Okey Table (Hourly)', 'ru' => 'Стол для окей (почасово)'], 'description' => ['tr' => '4 kişilik masa kiralama', 'en' => '4-player table rental', 'ru' => 'Аренда стола на 4 человека'], 'price' => 200],
                ['category' => 'okey', 'name' => ['tr' => 'Okey + İçecek Paketi', 'en' => 'Okey + Drinks Pack', 'ru' => 'Окей + напитки'], 'description' => ['tr' => '2 saat okey + 4 içecek', 'en' => '2 hours okey + 4 drinks', 'ru' => '2 часа окей + 4 напитка'], 'price' => 550, 'badge' => 'Paket'],
            ];

            $sort = 0;
            foreach ($products as $p) {
                $cat = Category::where('slug', $p['category'])->first();
                Product::updateOrCreate(
                    [
                        'restaurant_id' => $restaurant->id,
                        'category_id' => $cat->id,
                        'name->tr' => $p['name']['tr'],
                    ],
                    [
                        'type' => $p['category'] === 'icecek' ? 'bar' : 'kitchen',
                        'name' => $p['name'],
                        'description' => $p['description'],
                        'price' => $p['price'],
                        'badge' => $p['badge'] ?? null,
                        'sort_order' => $sort++,
                        'is_available' => true,
                    ]
                );
            }

            $this->seedProductOptions($restaurant);

            for ($i = 1; $i <= 8; $i++) {
                Table::updateOrCreate(
                    ['restaurant_id' => $restaurant->id, 'number' => (string) $i],
                    ['qr_token' => Str::random(16), 'is_active' => true]
                );
            }
        });
    }

    private function seedProductOptions(Restaurant $restaurant): void
    {
        $burger = Product::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('name->tr', 'Human Burger')
            ->first();

        if ($burger) {
            $sizeGroup = ProductOptionGroup::updateOrCreate(
                ['product_id' => $burger->id, 'name->tr' => 'Boy'],
                [
                    'restaurant_id' => $restaurant->id,
                    'name' => ['tr' => 'Boy', 'en' => 'Size', 'ru' => 'Размер'],
                    'type' => ProductOptionGroup::TYPE_SINGLE,
                    'required' => true,
                    'sort_order' => 1,
                ],
            );

            $extrasGroup = ProductOptionGroup::updateOrCreate(
                ['product_id' => $burger->id, 'name->tr' => 'Ekstralar'],
                [
                    'restaurant_id' => $restaurant->id,
                    'name' => ['tr' => 'Ekstralar', 'en' => 'Extras', 'ru' => 'Дополнения'],
                    'type' => ProductOptionGroup::TYPE_MULTIPLE,
                    'required' => false,
                    'sort_order' => 2,
                ],
            );

            $sizeOptions = [
                ['tr' => 'Normal', 'en' => 'Regular', 'ru' => 'Обычный', 'price' => 0, 'default' => true, 'sort' => 1],
                ['tr' => 'Büyük Boy', 'en' => 'Large', 'ru' => 'Большой', 'price' => 40, 'default' => false, 'sort' => 2],
            ];

            foreach ($sizeOptions as $opt) {
                ProductOption::updateOrCreate(
                    ['product_option_group_id' => $sizeGroup->id, 'name->tr' => $opt['tr']],
                    [
                        'name' => ['tr' => $opt['tr'], 'en' => $opt['en'], 'ru' => $opt['ru']],
                        'price_modifier' => $opt['price'],
                        'is_default' => $opt['default'],
                        'sort_order' => $opt['sort'],
                    ],
                );
            }

            $extraOptions = [
                ['tr' => 'Ekstra Cheddar', 'en' => 'Extra Cheddar', 'ru' => 'Доп. чеддер', 'price' => 35, 'sort' => 1],
                ['tr' => 'Ekstra Sos', 'en' => 'Extra Sauce', 'ru' => 'Доп. соус', 'price' => 15, 'sort' => 2],
                ['tr' => 'Jalapeño', 'en' => 'Jalapeño', 'ru' => 'Халапеньо', 'price' => 20, 'sort' => 3],
            ];

            foreach ($extraOptions as $opt) {
                ProductOption::updateOrCreate(
                    ['product_option_group_id' => $extrasGroup->id, 'name->tr' => $opt['tr']],
                    [
                        'name' => ['tr' => $opt['tr'], 'en' => $opt['en'], 'ru' => $opt['ru']],
                        'price_modifier' => $opt['price'],
                        'is_default' => false,
                        'sort_order' => $opt['sort'],
                    ],
                );
            }
        }

        $latte = Product::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('name->tr', 'Latte')
            ->first();

        if ($latte) {
            $sizeGroup = ProductOptionGroup::updateOrCreate(
                ['product_id' => $latte->id, 'name->tr' => 'Boy'],
                [
                    'restaurant_id' => $restaurant->id,
                    'name' => ['tr' => 'Boy', 'en' => 'Size', 'ru' => 'Размер'],
                    'type' => ProductOptionGroup::TYPE_SINGLE,
                    'required' => true,
                    'sort_order' => 1,
                ],
            );

            $latteSizes = [
                ['tr' => 'Küçük', 'en' => 'Small', 'ru' => 'Маленький', 'price' => 0, 'default' => true, 'sort' => 1],
                ['tr' => 'Orta', 'en' => 'Medium', 'ru' => 'Средний', 'price' => 10, 'default' => false, 'sort' => 2],
                ['tr' => 'Büyük', 'en' => 'Large', 'ru' => 'Большой', 'price' => 20, 'default' => false, 'sort' => 3],
            ];

            foreach ($latteSizes as $opt) {
                ProductOption::updateOrCreate(
                    ['product_option_group_id' => $sizeGroup->id, 'name->tr' => $opt['tr']],
                    [
                        'name' => ['tr' => $opt['tr'], 'en' => $opt['en'], 'ru' => $opt['ru']],
                        'price_modifier' => $opt['price'],
                        'is_default' => $opt['default'],
                        'sort_order' => $opt['sort'],
                    ],
                );
            }
        }
    }
}
