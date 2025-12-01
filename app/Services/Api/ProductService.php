<?php

namespace App\Services\Api;

use App\Models\Product;
use App\Traits\ApiTrait;
use Illuminate\Support\Facades\Log;

class ProductService
{
    // created by kariem ibrahiem
    use ApiTrait;
    // get all dependencies i might need
    public function __construct(protected Product $model)
    {
        Log::info('ProductService initialized by user ' . auth('client-api')->id());
    }

    // get all products
    public function getProducts()
    {
        $products = $this->model->get();
        Log::info('Fetched all products');
        return $this->respondWithSuccess($products);    
    }

    // get product by id
    public function getProductById($id)
    {
        $product = $this->model->find($id);
        if (!$product) {
            return $this->respondWithError('Product not found', 404);
        }
        Log::info('Fetched product: ' . $product->id);
        return $this->respondWithSuccess($product);
    }
}
