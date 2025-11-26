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
        Schema::create('agents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('telephone')->unique();
            $table->string('adresse');
            $table->string('ville');
            $table->string('code_postal');
            $table->string('pays')->default('Sénégal');
            $table->enum('statut', ['actif', 'inactif', 'suspendu'])->default('actif');
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
