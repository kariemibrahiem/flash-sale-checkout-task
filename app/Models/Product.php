<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'reserved_stock',
    ];

    protected $casts = [
        'stock' => 'integer',
        'reserved_stock' => 'integer',
    ];
    
    public function orders()
    {
        return $this->belongsToMany(Order::class)->withPivot('quantity', 'price');
    }

    public function holds()
    {
        return $this->hasMany(Hold::class);
    }
}
