<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cierres_caja', function (Blueprint $table) {
            $table->id();
            // Usuario (caja) que hizo el cierre
            $table->foreignId('cajero_id')->constrained('users')->restrictOnDelete();
            // Un cierre por día
            $table->date('fecha')->unique();
            $table->decimal('efectivo_esperado', 10, 2)->default(0);
            $table->decimal('tarjeta_esperado', 10, 2)->default(0);
            $table->decimal('transferencia_esperado', 10, 2)->default(0);
            $table->decimal('efectivo_contado', 10, 2)->default(0);
            // efectivo_contado - efectivo_esperado
            $table->decimal('diferencia', 10, 2)->default(0);
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cierres_caja');
    }
};
