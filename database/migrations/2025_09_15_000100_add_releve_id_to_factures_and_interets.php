<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('factures', function (Blueprint $table) {
            if (!Schema::hasColumn('factures', 'releve_id')) {
                $table->foreignId('releve_id')->nullable()->constrained('releves')->nullOnDelete()->after('client_id');
            }
        });

        Schema::table('interets', function (Blueprint $table) {
            if (!Schema::hasColumn('interets', 'releve_id')) {
                $table->foreignId('releve_id')->nullable()->constrained('releves')->nullOnDelete()->after('facture_id');
            }
        });
    }

    public function down()
    {
        Schema::table('interets', function (Blueprint $table) {
            if (Schema::hasColumn('interets', 'releve_id')) {
                $table->dropConstrainedForeignId('releve_id');
            }
        });

        Schema::table('factures', function (Blueprint $table) {
            if (Schema::hasColumn('factures', 'releve_id')) {
                $table->dropConstrainedForeignId('releve_id');
            }
        });
    }
};


