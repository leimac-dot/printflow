<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Printer extends Model
{
    protected $fillable = [
        'name',
        'model',
        'power_watts',
        'purchase_price',
        'lifespan_hours',
        'maintenance_cost_per_hour',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'power_watts' => 'float',
            'purchase_price' => 'float',
            'lifespan_hours' => 'float',
            'maintenance_cost_per_hour' => 'float',
            'is_active' => 'boolean',
        ];
    }
}
