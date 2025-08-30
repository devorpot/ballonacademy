<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('extra_classes', function (Blueprint $table) {
            $table->id();
            $table->string('title');                         // required
            $table->string('extract');                       // required
            $table->longText('description');                 // required

            $table->string('image')->nullable();             // path de imagen
            $table->string('cover')->nullable();             // path de cover

            // Notación solicitada: 1 = Sí, 2 = No (por eso tinyInteger)
            $table->tinyInteger('is_youtube')->default(2);   // default No
            $table->string('youtube_url')->nullable();

            $table->string('video')->nullable();             // path del mp4
            $table->string('subt_str')->nullable();          // path del .vtt

            $table->string('category')->nullable();
            $table->string('tags')->nullable();              // CSV o libre

            $table->tinyInteger('active')->default(1);       // 1 = Sí, 2 = No
            $table->integer('order')->default(0);

            $table->timestamps();

            $table->index(['active', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extra_classes');
    }
};
