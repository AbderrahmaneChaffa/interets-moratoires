<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('releves', function (Blueprint $table) {
            if (!Schema::hasColumn('releves', 'reference')) {
                $table->string('reference')->after('client_id');
            }
            if (!Schema::hasColumn('releves', 'categorie')) {
                $table->string('categorie')->nullable()->after('date_creation');
            }
            if (!Schema::hasColumn('releves', 'montant_total_ht')) {
                $table->decimal('montant_total_ht', 15, 2)->default(0)->after('categorie');
            }
        });

        // Convert enum statut to VARCHAR if needed and set default to 'En attente'
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `releves` MODIFY `statut` VARCHAR(20) NOT NULL DEFAULT 'En attente'");
        } else {
            DB::statement("ALTER TABLE releves ALTER COLUMN statut SET DEFAULT 'En attente'");
        }
    }

    public function down()
    {
        // We won't attempt to convert back enum to avoid DBAL; just drop added columns
        Schema::table('releves', function (Blueprint $table) {
            if (Schema::hasColumn('releves', 'montant_total_ht')) {
                $table->dropColumn('montant_total_ht');
            }
            if (Schema::hasColumn('releves', 'categorie')) {
                $table->dropColumn('categorie');
            }
            if (Schema::hasColumn('releves', 'reference')) {
                $table->dropColumn('reference');
            }
        });
        // Default for statut back to 'Impayé' if needed
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `releves` MODIFY `statut` VARCHAR(20) NOT NULL DEFAULT 'Impayé'");
        } else {
            DB::statement("ALTER TABLE releves ALTER COLUMN statut SET DEFAULT 'Impayé'");
        }
    }
};


