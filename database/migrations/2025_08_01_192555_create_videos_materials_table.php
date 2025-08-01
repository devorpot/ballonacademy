<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideosMaterialsTable extends Migration
{
    public function up()
    {
        Schema::create('videos_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('video_id');
            $table->string('name'); // Nombre del material
            $table->string('quantity')->nullable(); // Cantidad recomendada (ej. "2 metros", "3 piezas")
            $table->string('unit')->nullable();      // Unidad (opcional, ej. "metros", "litros")
            $table->text('notes')->nullable();       // Notas extra (opcional)
            $table->string('image')->nullable();     // URL o path de la imagen
            $table->decimal('price', 10, 2)->nullable(); // Precio del material
            $table->string('buy_link')->nullable();  // Enlace de compra (opcional)
            $table->timestamps();

            // Foreign Key
            $table->foreign('video_id')->references('id')->on('videos')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('videos_materials');
    }
}
