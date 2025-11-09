<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Standardize factures.statut to ENUM('Payé','Impayé') default 'Impayé'
        try {
            DB::statement("ALTER TABLE `factures` MODIFY `statut` ENUM('Payé','Impayé') NOT NULL DEFAULT 'Impayé'");
        } catch (\Throwable $e) {
            // Fallback: ensure default at least if ENUM not supported
            DB::statement("ALTER TABLE `factures` MODIFY `statut` VARCHAR(20) NOT NULL DEFAULT 'Impayé'");
        }

        // Replace any legacy values
        DB::table('factures')->where('statut', 'Payée')->update(['statut' => 'Payé']);
        DB::table('factures')->where('statut', 'Impayée')->update(['statut' => 'Impayé']);
        DB::table('factures')->where('statut', 'En attente')->update(['statut' => 'Impayé']);
        DB::table('factures')->where('statut', 'Retard de paiement')->update(['statut' => 'Impayé']);
        DB::table('factures')->whereNull('statut')->update(['statut' => 'Impayé']);
    }

    public function down(): void
    {
        // Revert to broader VARCHAR with default 'Impayé'
        DB::statement("ALTER TABLE `factures` MODIFY `statut` VARCHAR(20) NOT NULL DEFAULT 'Impayé'");
    }
};

