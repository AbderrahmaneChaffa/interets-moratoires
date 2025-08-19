<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->decimal('interets_ht', 15, 2)->default(0.00)->after('interets');
            $table->decimal('interets_ttc', 15, 2)->default(0.00)->after('interets_ht');
        });
    }

    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropColumn(['interets_ht', 'interets_ttc']);
        });
    }
};
