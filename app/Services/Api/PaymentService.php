<?php

namespace App\Services\Api;

use App\Models\PaymentLog;
use App\Models\Order;
use App\Traits\PayMobTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    use PayMobTrait;
    public function __construct(protected PaymentLog $model , protected Order $orderModel) {
        Log::info('PaymentService initialized by user ' . auth('client-api')->id());
    }

    public function checkout()
    {
        $user_id = auth('client-api')->id();

        $order = $this->orderModel->where('user_id', $user_id)->where('status', 'pre-paid')->first();

        if (!$order) {
            return response()->json(['error' => 'No pre-paid order found'], 404);
        }

        $paymentUrl = $this->generatePaymentUrl($order->grand_total, $order->id, Order::class, 'order');
        Log::info('Payment URL generated for order: ' . $order->id . ' User: ' . $user_id);
        return response()->json(['payment_url' => $paymentUrl]);
    }
    // handle payment webhook
    public function webhook(array $data)
    {
        $idempotencyKey = $data['idempotency_key'] ?? null;

        if (!$idempotencyKey) {
            return response()->json(['error' => 'Idempotency key missing'], 400);
        }

        $existingLog = $this->model->where('idempotency_key', $idempotencyKey)->first();

        if ($existingLog) {
            return response()->json(['success' => true, 'message' => 'Duplicate webhook ignored']);
        }

        try {
            DB::transaction(function () use ($data, $idempotencyKey) {
                $order = $this->orderModel->find($data['order_id']);

                if (!$order) {
                    throw new \Exception('Order not found');
                }

                $order->status = $data['status'] === 'completed' ? 'paid' : 'cancelled';
                $order->save();

                $this->model->create([
                    'order_id' => $order->id,
                    'idempotency_key' => $idempotencyKey,
                    'status' => $order->status,
                    'payload' => json_encode($data),
                ]);
            });
            Log::info('Payment webhook processed for order: ' . $data['order_id'] . ' with idempotency key: ' . $idempotencyKey);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error("Payment Webhook Error: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
