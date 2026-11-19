<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('project_name');
            $table->decimal('print_time_hours', 8, 2);
            $table->decimal('filament_grams', 8, 2)->default(0);
            $table->decimal('design_cost', 8, 2)->default(0);       // <-- Añadir
            $table->decimal('post_process_cost', 8, 2)->default(0); // <-- Añadir
            $table->decimal('packaging_cost', 8, 2)->default(0);    // <-- Añadir
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->decimal('final_price', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};