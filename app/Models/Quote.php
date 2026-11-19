<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $fillable = [
        'project_name',
        'print_time_hours',
        'filament_grams',
        'design_cost',      // Nuevo: Costo de diseño/preparación
        'post_process_cost', // Nuevo: Costo de post-procesado
        'packaging_cost',    // Nuevo: Costo de empaque/caja
        'total_cost',
        'final_price',
    ];
}