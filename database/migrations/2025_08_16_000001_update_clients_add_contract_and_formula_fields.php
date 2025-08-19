<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('contrat_maintenance')->nullable()->after('raison_sociale');
            $table->text('formule')->nullable()->after('contrat_maintenance');
            $table->decimal('taux', 5, 2)->nullable()->after('formule'); // Exemple: 0.09 pour 9%
            $table->string('ai')->nullable()->after('rc');
            // nif, rc, adresse existent déjà
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['contrat_maintenance', 'formule', 'taux', 'ai']);
        });
    }
};
