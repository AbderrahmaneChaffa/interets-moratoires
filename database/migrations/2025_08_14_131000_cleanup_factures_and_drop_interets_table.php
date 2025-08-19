<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Supprimer la colonne statut_paiement si elle existe
        if (Schema::hasColumn('factures', 'statut_paiement')) {
            Schema::table('factures', function (Blueprint $table) {
                $table->dropColumn('statut_paiement'); // Champ supprimé car non utilisé (doublon de statut)
            });
        }

        // Uniformiser le type des montants: interets en DECIMAL(15,2)
        if (Schema::hasColumn('factures', 'interets')) {
            DB::statement("ALTER TABLE factures MODIFY interets DECIMAL(15,2) NOT NULL DEFAULT 0.00");
        }

        // Supprimer la table interets si elle n'est pas utilisée
        if (Schema::hasTable('interets')) {
            Schema::drop('interets'); // Table supprimée car logique d'intérêts déplacée dans factures.interets
        }
    }

    public function down(): void
    {
        // Impossible de restaurer proprement sans informations historiques; on recrée minimalement les éléments
        if (!Schema::hasColumn('factures', 'statut_paiement')) {
            Schema::table('factures', function (Blueprint $table) {
                $table->string('statut_paiement')->nullable();
            });
        }

        if (Schema::hasColumn('factures', 'interets')) {
            DB::statement("ALTER TABLE factures MODIFY interets DECIMAL(10,2) NOT NULL DEFAULT 0.00");
        }

        if (!Schema::hasTable('interets')) {
            Schema::create('interets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('facture_id')->constrained('factures')->onDelete('cascade');
                $table->date('date_calcul');
                $table->integer('jours_retards');
                $table->decimal('interet_ht', 15, 2);
                $table->decimal('interet_ttc', 15, 2);
                $table->decimal('taux_utilise', 8, 4);
                $table->timestamps();
            });
        }
    }
};
