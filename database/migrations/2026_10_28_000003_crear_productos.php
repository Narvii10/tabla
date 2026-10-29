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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained()->restrictOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->decimal('costo', 10, 2)->nullable();
            $table->string('foto')->nullable();
            // false = agotado
            $table->boolean('disponible')->default(true);
            $table->boolean('es_vino')->default(false);
            // Vino sugerido (maridaje): otro producto con es_vino = true
            $table->foreignId('vino_sugerido_id')->nullable()->constrained('productos')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
