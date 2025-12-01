<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Product;
use App\Models\Hold;

class HoldFactory extends Factory
{
    protected $model = Hold::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'quantity' => $this->faker->numberBetween(1, 5),
            'grand_total' => $this->faker->numberBetween(100, 1000),
            'expires_at' => now()->addMinutes(5),
            'used' => false,
        ];
    }
}
