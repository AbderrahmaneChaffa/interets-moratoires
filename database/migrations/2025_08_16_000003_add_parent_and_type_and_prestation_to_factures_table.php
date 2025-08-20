<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after(column: 'client_id');
            $table->string('type')->default('principale')->after('parent_id'); // 'principale' | 'interet'
            $table->string('prestation')->nullable()->after('reference');

            $table->foreign('parent_id')->references('id')->on('factures')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropForeign(index: ['parent_id']);
            $table->dropColumn(['parent_id','type','prestation']);
        });
    }
};
