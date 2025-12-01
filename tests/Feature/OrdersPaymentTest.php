<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Hold;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdersPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_example(): void
    {
        $this->assertTrue(true);
    }

    public function test_user_can_hold_order()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);

        $this->actingAs($user, 'client-api')
            ->postJson('/api/v1/orders/hold', [
                'product_id' => $product->id,
                'quantity' => 3
            ])
            ->assertStatus(200)
            ->assertJson([
                'status' => 200,
                'message' => 'Order held successfully'
            ]);
    }



    public function test_parallel_holds_no_oversell()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);

        $response1 = $this->actingAs($user1, 'client-api')
            ->postJson('/api/v1/orders/hold', ['product_id' => $product->id, 'quantity' => 3]);

        $response2 = $this->actingAs($user2, 'client-api')
            ->postJson('/api/v1/orders/hold', ['product_id' => $product->id, 'quantity' => 3]);

        $response1->assertStatus(200);
        $response2->assertStatus(400);

        $this->assertEquals(3, Product::find($product->id)->reserved_stock);
        $this->assertEquals(5, Product::find($product->id)->stock);
    }

    public function test_hold_expiry_returns_availability()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5, 'reserved_stock' => 0]);

        Hold::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'grand_total' => 300,
            'expires_at' => now()->subMinutes(1)
        ]);

        $response = $this->actingAs($user, 'client-api')
            ->postJson('/api/v1/orders/hold', [
                'product_id' => $product->id,
                'quantity' => 3,
                'user_id' => $user->id,
            ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'product_id', 'quantity', 'expires_at']
            ])
            ->assertJson([
                'status' => 200,
                'message' => 'Order held successfully'
            ]);


        $product->refresh();
        $this->assertEquals(3, $product->reserved_stock);
    }

    public function test_webhook_idempotency_and_early_webhook()
    {
        $user = User::factory()->create();


        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pre-paid',
        ]);

        $idempotencyKey = 'unique-key-123';


        $response1 = $this->postJson('/api/v1/payments/webhook', [
            'order_id' => $order->id,
            'status' => 'completed',
            'idempotency_key' => $idempotencyKey
        ]);

        $response1->assertStatus(200)
            ->assertJson(['success' => true]);

        $response2 = $this->postJson('/api/v1/payments/webhook', [
            'order_id' => $order->id,
            'status' => 'completed',
            'idempotency_key' => $idempotencyKey
        ]);

        $response2->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Duplicate webhook ignored']);

        $response3 = $this->postJson('/api/v1/payments/webhook', [
            'order_id' => 99999,
            'status' => 'completed',
            'idempotency_key' => 'new-key-456'
        ]);

        $response3->assertStatus(500)
            ->assertJsonStructure(['error']);
    }
}
