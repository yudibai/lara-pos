<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_category_id',
        'name',
        'price',
        'image_product',
        'sku',
        'plu',
        'capital',
        'description',
    ];
}
