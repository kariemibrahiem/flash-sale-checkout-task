<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\ProductService;

class ProductsController extends Controller
{
    // created by kariem ibrahiem
    // get all dependencies i might need
    public function __construct(protected ProductService $productService)
    {
    }
    // get all products
    public function getProducts()
    {
        return $this->productService->getProducts();
    }
    // get product by id
    public function getProductById($id)
    {
        return $this->productService->getProductById($id);
    }
}
