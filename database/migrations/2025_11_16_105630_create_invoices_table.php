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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('releve_id')->nullable()->index();
            $table->string('path');
            $table->decimal('amount_ht', 15, 2)->default(0);
            $table->decimal('amount_ttc', 15, 2)->default(0);
            $table->string('status')->default('Impayé'); // unpaid / paid
            $table->timestamp('paid_at')->nullable();
            $table->json('meta')->nullable(); // ids de periodes etc.
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }



};
