<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    protected $fillable = [
        'user_id',
        'print_model_id',
        'client_email',
        'project_name',
        'print_time_hours',
        'filament_grams',
        'design_cost',
        'post_process_cost',
        'packaging_cost',
        'total_cost',
        'final_price',
        'status',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function printModel(): BelongsTo
    {
        return $this->belongsTo(PrintModel::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(QuoteStatusHistory::class)->latest();
    }
}