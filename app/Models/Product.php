<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $fillable = [
        'name_product',
        'slug_product',
        'code_product',
        'qr_code_product',
        'description_product',
        'image_product',
        'initial_stock',
        'adjustment',
        'final_stock',
        'stock_alert',
        'cost_price',
        'category_id',
        'unit_id',
        'warehouse_id',
    ];

    // Get the category that owns the product
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Get the unit that owns the product
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    // Get the warehouse that owns the product
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
