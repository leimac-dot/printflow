<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('printers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('power_watts', 8, 2);
            $table->decimal('cost', 10, 2);
            $table->decimal('lifespan_hours', 10, 2);
            $table->decimal('maintenance_cost', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('printers');
    }
};