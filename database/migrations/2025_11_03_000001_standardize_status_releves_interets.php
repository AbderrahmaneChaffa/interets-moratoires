<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Standardize releves.statut to ENUM('Payé','Impayé') default 'Impayé'
        try {
            DB::statement("ALTER TABLE `releves` MODIFY `statut` ENUM('Payé','Impayé') NOT NULL DEFAULT 'Impayé'");
        } catch (\Throwable $e) {
            // Fallback: ensure default at least if ENUM not supported
            DB::statement("ALTER TABLE `releves` MODIFY `statut` VARCHAR(20) NOT NULL DEFAULT 'Impayé'");
        }

        // Replace any legacy values
        DB::table('releves')->where('statut', 'Payée')->update(['statut' => 'Payé']);
        DB::table('releves')->where('statut', 'Impayée')->update(['statut' => 'Impayé']);
        DB::table('releves')->where('statut', 'En attente')->update(['statut' => 'Impayé']);

        // Standardize interets.statut to ENUM('Payé','Impayé') default 'Impayé'
        try {
            DB::statement("ALTER TABLE `interets` MODIFY `statut` ENUM('Payé','Impayé') NOT NULL DEFAULT 'Impayé'");
        } catch (\Throwable $e) {
            DB::statement("ALTER TABLE `interets` MODIFY `statut` VARCHAR(20) NOT NULL DEFAULT 'Impayé'");
        }

        DB::table('interets')->where('statut', 'Payée')->update(['statut' => 'Payé']);
        DB::table('interets')->where('statut', 'Impayée')->update(['statut' => 'Impayé']);
        DB::table('interets')->whereNull('statut')->update(['statut' => 'Impayé']);
    }

    public function down(): void
    {
        // Revert to broader VARCHAR with default 'Impayé'
        DB::statement("ALTER TABLE `releves` MODIFY `statut` VARCHAR(20) NOT NULL DEFAULT 'Impayé'");
        DB::statement("ALTER TABLE `interets` MODIFY `statut` VARCHAR(20) NOT NULL DEFAULT 'Impayé'");
    }
};


