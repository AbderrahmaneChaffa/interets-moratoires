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
        Schema::table('interets', function (Blueprint $table) {
            $table->string('reference')->after('facture_id')->nullable();
            $table->string('pdf_path')->after('interet_ttc')->nullable();
            $table->enum('statut', ['Payée','Impayée'])->default('Impayée')->after('date_fin_periode');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('interets', function (Blueprint $table) {
            //
        });
    }
};
