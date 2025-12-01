<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\OrdersService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // created by kariem ibrahiem
    public function __construct(protected OrdersService $ordersService)
    {
    }
    // get all orders
    public function getOrders()
    {
        return $this->ordersService->getOrders();
    }
    
    // create order
    public function createOrder(Request $request)
    {
        return $this->ordersService->createOrder($request->all());
    }

    // hold order
    public function holdOrder(Request $request)
    {
        return $this->ordersService->holdOrder($request->all());
    }

    
}
