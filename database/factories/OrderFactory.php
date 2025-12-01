<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Order;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), 
            'status' => $this->faker->randomElement(['pre-paid', 'paid', 'cancelled']),
            'grand_total' => $this->faker->numberBetween(50, 5000),
            'quantity' => $this->faker->numberBetween(1, 10),
            'product_id' => \App\Models\Product::factory(),
            'hold_id' => \App\Models\Hold::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];  
    }

    /**
     * حالة Order مكتملة الدفع.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
        ]);
    }

    /**
     * حالة Order ملغاة.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
