<?php

// database/migrations/xxxx_xx_xx_add_extra_fields_to_profiles_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('full_name')->nullable();
            $table->string('personal_email')->nullable()->unique();
            $table->string('country')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('nickname')->nullable()->unique();
            $table->string('profile_image')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('website')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('tiktok')->nullable();
            $table->string('youtube')->nullable();
            $table->longText('description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'full_name',
                'personal_email',
                'country',
                'whatsapp',
                'nickname',
                'profile_image',
                'cover_image',
                'website',
                'facebook',
                'instagram',
                'tiktok',
                'youtube',
                'description'
            ]);
        });
    }
};
