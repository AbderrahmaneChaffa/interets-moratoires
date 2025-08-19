<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('factures', function (Blueprint $table) {
            // Supprimer les colonnes liées aux intérêts qui ne sont plus nécessaires
            $table->dropColumn([
                'interets',
                'interets_ht', 
                'interets_ttc'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->decimal('interets', 15, 2)->default(0.00);
            $table->decimal('interets_ht', 15, 2)->default(0.00);
            $table->decimal('interets_ttc', 15, 2)->default(0.00);
        });
    }
};
