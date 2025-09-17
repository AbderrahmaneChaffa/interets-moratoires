<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `interets` MODIFY `facture_id` BIGINT UNSIGNED NULL');
        } else {
            DB::statement('ALTER TABLE interets MODIFY facture_id BIGINT UNSIGNED NULL');
        }
    }

    public function down()
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `interets` MODIFY `facture_id` BIGINT UNSIGNED NOT NULL');
        } else {
            DB::statement('ALTER TABLE interets MODIFY facture_id BIGINT UNSIGNED NOT NULL');
        }
    }
};


