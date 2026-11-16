<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $title) {
            $title->id();
            $title->string('project_name');
            $title->decimal('print_time_hours', 8, 2);
            $title->decimal('filament_grams', 8, 2);
            $title->decimal('total_cost', 10, 2);
            $title->decimal('final_price', 10, 2);
            $title->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};