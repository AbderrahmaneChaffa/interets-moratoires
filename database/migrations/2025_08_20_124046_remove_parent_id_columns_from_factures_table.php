<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn([
                'parent_id',
                'type',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('type')->default('principale');

            // Re-add the foreign key if it existed before
            $table->foreign('parent_id')
                ->references('id')
                ->on('factures')
                ->onDelete('cascade');
        });
    }
};
