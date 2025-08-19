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
        Schema::table('factures', function (Blueprint $table) {
            $table->enum('statut', ['En attente', 'Payée', 'Retard de paiement', 'Impayée'])->default('En attente')->after('statut_paiement');
            $table->decimal('interets', 10, 2)->default(0.00)->after('statut');
            $table->integer('delai_legal_jours')->default(30)->after('interets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropColumn(['statut', 'interets', 'delai_legal_jours']);
        });
    }
};
