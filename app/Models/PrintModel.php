<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrintModel extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image_path',
        'filament_grams',
        'print_hours',
        'print_minutes',
    ];
}