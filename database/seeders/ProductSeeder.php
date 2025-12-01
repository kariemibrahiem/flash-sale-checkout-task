<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    // created by kariem ibrahiem
    public function run(): void
    {
        Product::factory()->count(10)->create();

        Product::create([
            'name' => 'Flash Sale Special Product',
            'description' => 'This is the primary flash-sale product used for testing concurrency.',
            'price' => 199.99,
            'stock' => 50,       
            'reserved_stock' => 0
        ]);
    }
}
