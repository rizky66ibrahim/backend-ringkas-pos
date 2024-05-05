<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;
    protected $table = 'units';
    protected $fillable = [
        'name_unit',
        'description',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
