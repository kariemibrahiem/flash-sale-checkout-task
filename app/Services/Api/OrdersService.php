<?php

namespace App\Services\Api;

use App\Models\Hold;
use App\Models\Order;
use App\Models\Product;
use App\Traits\ApiTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrdersService
{
    //created by kariem ibrahiem 
    use ApiTrait;
    //get all dependencies i might need 
    public function __construct(protected Order $model, protected Hold $holdModel, protected Product $productModel)
    {
        Log::info('OrdersService initialized by user ' . auth('client-api')->id());
    }

    //get user orders 
    public function getOrders()
    {
        $user_id = auth('client-api')->id();
        $orders = $this->model->where("user_id", $user_id)->get();
        Log::info('Fetched orders for user: ' . $user_id);
        return $this->respondWithSuccess($orders);
    }

    // create order from hold
    public function createOrder($data)
    {
        $data['user_id'] = auth('client-api')->id();
        $data['user_id'] = (int)$data['user_id'];


        $hold = $this->holdModel
            ->where('id', $data['hold_id'])
            ->where('user_id', $data['user_id'])
            ->where('used', 0)
            ->where('expires_at', '>', Carbon::now())
            ->latest()->first();


        if (!$hold) {
            return $this->respondWithError('Invalid or expired hold', 400);
        }
        try {
            return DB::transaction(function () use ($data, $hold) {

                $hold->used = true;
                $hold->save();

                $product = $hold->product()->lockForUpdate()->first();
                if (!$product) {
                    throw new \Exception('Product not found');
                }

                $order = $this->model->create([
                    'user_id' => $data['user_id'],
                    'product_id' => $hold->product_id,
                    'hold_id' => $hold->id,
                    'quantity' => $hold->quantity,
                    'grand_total' => $hold->grand_total,
                    'status' => 'pre-paid',
                ]);


                $product->reserved_stock -= $hold->quantity;
                $product->stock -= $hold->quantity;
                $product->reserved_stock = max($product->reserved_stock, 0);
                $product->save();
                Log::info('Order created: ' . $order->id . ' for user: ' . $data['user_id']);
                return $this->respondWithSuccess($order, 'Order created successfully');
            });
        } catch (\Exception $e) {
            Log::error('Order creation failed for user: ' . $data['user_id'] . ' Error: ' . $e->getMessage());
            return $this->respondWithError($e->getMessage(), 400);
        }
    }

    // hold order
    public function holdOrder($data)
    {
        $data['user_id'] = auth('client-api')->id();

        try {
            $hold = DB::transaction(function () use ($data) {
                $product = $this->productModel->where('id', $data['product_id'])->lockForUpdate()->first();

                if (!$product) {
                    throw new \Exception('Product not found');
                }

                if ($product->stock - $product->reserved_stock < $data['quantity']) {
                    throw new \Exception('Insufficient stock to hold the order');
                }

                $product->reserved_stock += $data['quantity'];

                $product->save();
                $data['grand_total'] = $product->price * $data['quantity'];
                $data['expires_at'] = now()->addMinutes(5);
                return $this->holdModel->create($data);
            });
            Log::info('Hold created: ' . $hold->id . ' for user: ' . $data['user_id']);
            return $this->respondWithSuccess($hold, 'Order held successfully');
        } catch (\Exception $e) {
            Log::error('Hold creation failed for user: ' . $data['user_id'] . ' Error: ' . $e->getMessage());
            return $this->respondWithError($e->getMessage(), 400);
        }
    }
}
