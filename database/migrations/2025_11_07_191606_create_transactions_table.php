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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sender_compte_id')->constrained('comptes');
            $table->foreignUuid('receiver_client_id')->nullable()->constrained('comptes');
            $table->foreignUuid('receiver_partenaire_id')->nullable()->constrained('service_partenaires');
            $table->foreignUuid('receiver_marchant_id')->nullable()->constrained('marchants');
            $table->decimal('montant', 15, 2);
            $table->enum('type', ['depot', 'retrait', 'transfert', 'achat']);
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->foreignUuid('agent_id')->nullable()->constrained('agents')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
