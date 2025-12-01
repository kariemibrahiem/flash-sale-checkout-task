<?php

namespace App\Jobs;

use App\Models\Hold;
use Illuminate\Support\Facades\DB;

class ClearExpireHolds
{
    // created by kariem ibrahiem
    public function __construct()
    {
    }
    public function handle(): void
    {
        Hold::where('expires_at', '<=', now())
            ->where('used', false)
            ->chunk(50, function ($holds) {
                foreach ($holds as $hold) {
                    DB::transaction(function () use ($hold) {
                        $product = $hold->product()->lockForUpdate()->first();

                        if ($product) {
                            $product->reserved_stock -= $hold->quantity;
                            $product->reserved_stock = max($product->reserved_stock, 0);
                            $product->save();
                        }

                        $hold->delete();
                    });
                }
            });
    }
}
