<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filaments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('material_type');
            $table->decimal('cost_per_kg', 10, 2);
            $table->decimal('weight_grams', 8, 2)->default(1000);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filaments');
    }
};