<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;


class ProductFactory extends Factory
{
    // created by kariem ibrahiem
    public function definition(): array
    {
        $stock = $this->faker->numberBetween(20, 200);
        return [
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 50, 500),
            'stock' => $stock,
            'reserved_stock' => 0, 
        ];
    }
}
