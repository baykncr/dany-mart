<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Insert dulu tanpa association
        $categories = [
            ['name' => 'Minuman',            'code' => 'MNM', 'priority_order' => 1],
            ['name' => 'Makanan Ringan',     'code' => 'MKR', 'priority_order' => 2],
            ['name' => 'Produk Susu',        'code' => 'PSU', 'priority_order' => 3],
            ['name' => 'Rokok',              'code' => 'RKK', 'priority_order' => 4],
            ['name' => 'Perlengkapan Mandi', 'code' => 'PLM', 'priority_order' => 5],
            ['name' => 'Bumbu & Masakan',    'code' => 'BMK', 'priority_order' => 6],
            ['name' => 'Kebersihan Rumah',   'code' => 'KBR', 'priority_order' => 7],
            ['name' => 'Kesehatan',          'code' => 'KSH', 'priority_order' => 8],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // Setelah semua ada, set association antar kategori
        // Logic: beli minuman → rekomendasikan makanan ringan & snack
        $minuman        = Category::where('code', 'MNM')->first();
        $makananRingan  = Category::where('code', 'MKR')->first();
        $susu           = Category::where('code', 'PSU')->first();
        $rokok          = Category::where('code', 'RKK')->first();
        $mandi          = Category::where('code', 'PLM')->first();
        $bumbu          = Category::where('code', 'BMK')->first();
        $kebersihan     = Category::where('code', 'KBR')->first();
        $kesehatan      = Category::where('code', 'KSH')->first();

        $minuman->update([
            'association_categories' => [$makananRingan->id, $susu->id],
        ]);
        $makananRingan->update([
            'association_categories' => [$minuman->id, $susu->id],
        ]);
        $susu->update([
            'association_categories' => [$makananRingan->id, $minuman->id],
        ]);
        $rokok->update([
            'association_categories' => [$minuman->id, $makananRingan->id],
        ]);
        $mandi->update([
            'association_categories' => [$kebersihan->id, $kesehatan->id],
        ]);
        $bumbu->update([
            'association_categories' => [$makananRingan->id, $minuman->id],
        ]);
        $kebersihan->update([
            'association_categories' => [$mandi->id, $kesehatan->id],
        ]);
        $kesehatan->update([
            'association_categories' => [$mandi->id, $kebersihan->id],
        ]);
    }
}