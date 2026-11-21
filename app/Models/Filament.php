<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Filament extends Model
{
    protected $fillable = [
        'name',
        'brand',
        'material',
        'color',
        'spool_weight_grams',
        'spool_price',
        'price_per_gram',
        'density',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'spool_weight_grams' => 'float',
            'spool_price' => 'float',
            'price_per_gram' => 'float',
            'density' => 'float',
            'is_active' => 'boolean',
        ];
    }
}
