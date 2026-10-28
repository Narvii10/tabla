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
        // Cada producto que se agrega a un pedido
        Schema::create('detalles_pedido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained()->cascadeOnDelete();
            // null hasta que el producto se envía a cocina
            $table->foreignId('comanda_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('producto_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('cantidad');
            // Precio copiado del producto al momento de la venta
            $table->decimal('precio_unitario', 10, 2);
            $table->string('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles_pedido');
    }
};
