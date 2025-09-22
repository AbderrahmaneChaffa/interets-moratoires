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
        Schema::table('releves', function (Blueprint $table) {
            $table->date('date_derniere_facture')->nullable()->after('montant_total_ht');
            $table->string('releve_pdf')->nullable()->after('date_derniere_facture');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('releves', function (Blueprint $table) {
            $table->dropColumn(['date_derniere_facture', 'releve_pdf']);
        });
    }
};
