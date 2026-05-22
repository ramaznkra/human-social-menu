<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CafeGallery;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class HumanSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@human.com'],
            [
                'name' => 'Human Admin',
                'password' => Hash::make('human2026'),
            ]
        );

        $defaults = [
            'venue_name' => 'Human',
            'venue_slogan' => 'Social People',
            'brand_mark' => 'HSP',
            'venue_tagline' => 'Human Social Person — Coffee, Community, Experiences.',
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
            ['name' => 'Yiyecek', 'slug' => 'yiyecek', 'image' => 'images/menu/categories/yiyecek.jpg', 'sort_order' => 1],
            ['name' => 'İçecek', 'slug' => 'icecek', 'image' => 'images/menu/categories/icecek.jpg', 'sort_order' => 2],
            ['name' => 'Nargile', 'slug' => 'nargile', 'image' => 'images/menu/categories/nargile.jpg', 'sort_order' => 3],
            ['name' => 'Okey', 'slug' => 'okey', 'image' => 'images/menu/categories/okey.jpg', 'sort_order' => 4],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat + ['is_active' => true, 'icon' => null]);
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
                ['image_path' => $card['image_path']],
                $card + ['is_active' => true]
            );
        }

        $products = [
            ['category' => 'yiyecek', 'name' => 'Human Burger', 'description' => 'Özel soslu, cheddar peynirli burger', 'price' => 320, 'badge' => 'Popüler'],
            ['category' => 'yiyecek', 'name' => 'Sosyal Tabağı', 'description' => 'Paylaşımlık atıştırmalık tabağı', 'price' => 280],
            ['category' => 'yiyecek', 'name' => 'Nachos', 'description' => 'Guacamole ve salsa ile', 'price' => 195],
            ['category' => 'icecek', 'name' => 'Espresso', 'description' => 'Tek shot', 'price' => 75],
            ['category' => 'icecek', 'name' => 'Latte', 'description' => 'Sütlü kahve', 'price' => 95],
            ['category' => 'icecek', 'name' => 'Limonata', 'description' => 'Taze sıkılmış', 'price' => 85],
            ['category' => 'icecek', 'name' => 'Mojito', 'description' => 'Alkolsüz ferah içecek', 'price' => 120, 'badge' => 'Yeni'],
            ['category' => 'nargile', 'name' => 'Elma Nargile', 'description' => 'Klasik elma aroması', 'price' => 450],
            ['category' => 'nargile', 'name' => 'Üzüm Nargile', 'description' => 'Yoğun üzüm aroması', 'price' => 450],
            ['category' => 'nargile', 'name' => 'Karışık Nargile', 'description' => 'İki aroma seçeneği', 'price' => 500],
            ['category' => 'okey', 'name' => 'Okey Masası (Saatlik)', 'description' => '4 kişilik masa kiralama', 'price' => 200],
            ['category' => 'okey', 'name' => 'Okey + İçecek Paketi', 'description' => '2 saat okey + 4 içecek', 'price' => 550, 'badge' => 'Paket'],
        ];

        $sort = 0;
        foreach ($products as $p) {
            $cat = Category::where('slug', $p['category'])->first();
            Product::updateOrCreate(
                ['category_id' => $cat->id, 'name' => $p['name']],
                [
                    'type' => $p['category'] === 'icecek' ? 'bar' : 'kitchen',
                    'description' => $p['description'],
                    'price' => $p['price'],
                    'badge' => $p['badge'] ?? null,
                    'sort_order' => $sort++,
                    'is_available' => true,
                ]
            );
        }

        for ($i = 1; $i <= 8; $i++) {
            Table::updateOrCreate(
                ['number' => (string) $i],
                ['qr_token' => Str::random(16), 'is_active' => true]
            );
        }

    }
}
