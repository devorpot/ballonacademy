<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('video_comments', function (Blueprint $table) {
            $table->unsignedBigInteger('reply_comment_id')
                  ->nullable()
                  ->after('comment');

            // Clave foránea hacia la misma tabla
            $table->foreign('reply_comment_id')
                  ->references('id')
                  ->on('video_comments')
                  ->onDelete('cascade'); 
        });
    }

    public function down(): void
    {
        Schema::table('video_comments', function (Blueprint $table) {
            $table->dropForeign(['reply_comment_id']);
            $table->dropColumn('reply_comment_id');
        });
    }
};
