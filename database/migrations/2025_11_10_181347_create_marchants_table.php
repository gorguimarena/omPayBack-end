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
        Schema::create('marchants', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('code_merchant')->unique();
            $table->string('nom_boutique');
            $table->text('adresse');
            $table->string('ville');
            $table->text('url_qr')->nullable();
            $table->string('telephone_service');
            $table->string('email_service')->nullable();
            $table->timestamp('date_activation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marchants');
    }
};
