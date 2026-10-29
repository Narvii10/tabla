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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained()->restrictOnDelete();
            // Usuario (caja) que registró el pago
            $table->foreignId('cajero_id')->constrained('users')->restrictOnDelete();
            // efectivo, tarjeta, transferencia
            $table->string('metodo', 20);
            $table->decimal('monto', 10, 2);
            // Solo en efectivo: lo que entregó el cliente y el vuelto
            $table->decimal('recibido', 10, 2)->nullable();
            $table->decimal('vuelto', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
