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
        Schema::create('interets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_id')->constrained('factures')->onDelete('cascade');
            $table->date('date_debut_periode');
            $table->date('date_fin_periode');
            $table->integer('jours_retard');
            $table->decimal('interet_ht', 15, 2);
            $table->decimal('interet_ttc', 15, 2);
            $table->timestamps();
            
            // Contrainte d'unicité sur la paire (facture_id, date_debut_periode, date_fin_periode)
            $table->unique(['facture_id', 'date_debut_periode', 'date_fin_periode'], 'unique_facture_periode');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('interets');
    }
};
