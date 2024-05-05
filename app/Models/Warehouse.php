<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;
    protected $table = 'warehouses';
    protected $fillable = [
        'name_warehouse',
        'address_warehouse',
        'phone_warehouse',
        'province',
        'regency',
        'district',
        'village',
        'description',
        'is_default',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
