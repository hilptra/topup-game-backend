<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mobileLegends = Game::create([
            'name' => 'Mobile Legends',
            'slug' => 'mobile-legends',
            'requires_server_id' => true,
            'is_active' => true,
        ]);

        Product::create([
            'game_id' => $mobileLegends->id,
            'name' => '86 Diamonds',
            'price' => 20000,
            'base_price' => 17000,
            'sort_order' => 1,
        ]);
        
        Product::create([
            'game_id' => $mobileLegends->id,
            'name' => '172 Diamonds',
            'price' => 38000,
            'base_price' => 33000,
            'sort_order' => 2,
        ]);

        $freeFire = Game::create([
            'name' => 'Free Fire',
            'slug' => 'free-fire',
            'requires_server_id' => false,
            'is_active' => true,
        ]);

        Product::create([
            'game_id' => $freeFire->id,
            'name' => '70 Diamonds',
            'price' => 10000,
            'base_price' => 8500,
            'sort_order' => 1,
        ]);

        Product::create([
            'game_id' => $freeFire->id,
            'name' => '140 Diamonds',
            'price' => 20000,
            'base_price' => 18500,
            'sort_order' => 1,
        ]);
    }
}
