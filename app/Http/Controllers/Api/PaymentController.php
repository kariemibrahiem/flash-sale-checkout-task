<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Api\PaymentService;


class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService)
    {
    }
    public function checkout(){
        return $this->paymentService->checkout();
    }
    public function webhook(Request $request)
    {
        $data = $request->all();
        return $this->paymentService->webhook($data);
    }
}
