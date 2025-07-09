<?php

// database/migrations/xxxx_xx_xx_create_profiles_table.php

 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfilesTable extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Tax and personal data fields
            $table->string('rfc')->nullable();
            $table->string('business_name')->nullable();       // Razón social
            $table->string('street')->nullable();
            $table->string('external_number')->nullable();     // Número exterior
            $table->string('internal_number')->nullable();     // Número interior
            $table->string('state')->nullable();
            $table->string('municipality')->nullable();
            $table->string('neighborhood')->nullable();        // Colonia
            $table->string('postal_code')->nullable();
            $table->string('billing_email')->nullable();       // Correo para factura
            $table->string('tax_regime')->nullable();          // Régimen fiscal
            $table->string('cfdi_use')->nullable();            // Uso de factura (CFDI)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
}
