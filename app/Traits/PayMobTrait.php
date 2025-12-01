<?php

namespace App\Traits;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

trait PayMobTrait
{
    protected function generatePaymentUrl($total_price, $order_id, $modelName, $module)
    {
        try {
            $user = auth('client-api')->user();

            $name = is_array($user->name) ? $user->name : explode(' ', $user->name);
            $first_name = $name[0] ?? '';
            $last_name = $name[1] ?? '';

            $authClient = new Client([
                'base_uri' => 'https://accept.paymob.com/api/',
                'timeout' => 5,
                'headers' => ['Content-Type' => 'application/json']
            ]);

            $authResponse = $authClient->post('auth/tokens', [
                'json' => [
                    'api_key' => config('services.paymob.api_key')
                ]
            ]);

            $authData = json_decode($authResponse->getBody()->getContents(), true);
            $token = $authData['token'] ?? null;

            if (!$token) {
                throw new Exception('Failed to get authentication token from PayMob.');
            }

            $paymentClient = new Client([
                'base_uri' => 'https://accept.paymob.com/api/',
                'timeout' => 5,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                ]
            ]);

            $paymentResponse = $paymentClient->post('ecommerce/payment-links', [
                'json' => [
                    'amount_cents' => (int)($total_price * 100),
                    'currency' => 'EGP',
                    'integration_id' => config('services.paymob.integration_id'), 
                    'lock_order_when_paid' => true,
                    'full_name' => trim($first_name . ' ' . $last_name),
                    'order_id' => $order_id,
                    'is_live' => false,
                    'payment_methods' => [config('services.paymob.integration_id')] 
                ]
            ]);


            $paymentData = json_decode($paymentResponse->getBody()->getContents(), true);

            Log::info("PayMob Payment URL generated for order ID: {$order_id} User: {$user->id}");
            $order = $paymentData['order'] ?? null;
            return [
                'success' => true,
                'payment_url' => $paymentData['url'] ?? $paymentData['client_url'] ?? null,
                'order_id' => $order,
            ];
        } catch (Exception $e) {
            Log::error("PayMob Error for user {$user->id}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
