<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeldCart extends Model
{
    use HasFactory;
    protected $fillable = [
'cart_id',
'product_name',
'product_id',
'description',
'price',
'discount_price',
'quantity',
'active_price'

    ];
}
