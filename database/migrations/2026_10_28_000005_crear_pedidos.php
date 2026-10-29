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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesa_id')->constrained()->restrictOnDelete();
            // Usuario (mesero) que abrió el pedido
            $table->foreignId('mesero_id')->constrained('users')->restrictOnDelete();
            // abierto, cerrado, cancelado
            $table->string('estado', 20)->default('abierto');
            $table->decimal('propina', 10, 2)->default(0);
            $table->timestamp('abierto_en')->nullable();
            $table->timestamp('cerrado_en')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
