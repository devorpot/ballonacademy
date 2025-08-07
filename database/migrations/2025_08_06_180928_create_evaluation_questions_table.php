<?php

// database/migrations/xxxx_xx_xx_create_evaluation_questions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained('evaluations')->onDelete('cascade');
            $table->boolean('type'); // 1 = Abierta, 0 = Opción múltiple
            $table->string('question');
            $table->string('option_1')->nullable();
            $table->string('option_2')->nullable();
            $table->string('option_3')->nullable();
            $table->string('option_4')->nullable();
            $table->string('option_5')->nullable();
            $table->text('response_text')->nullable(); // Solo si type == 1
            $table->unsignedTinyInteger('response_option')->nullable(); // Solo si type == 0. 1,2,3,4,5
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_questions');
    }
};
