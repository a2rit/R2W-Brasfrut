<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseQuotationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_quotation', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codSAP',15)->nullable();
            $table->string('code',15);
            $table->string('id_solicitante');
            $table->string('name_solicitante');
            $table->string('provider1')->nullable();
            $table->string('provider1_email')->nullable();
            $table->string('provider2')->nullable();
            $table->string('provider2_email')->nullable();
            $table->string('provider3')->nullable();
            $table->string('provider3_email')->nullable();
            $table->string('provider4')->nullable();
            $table->string('provider4_email')->nullable();
            $table->string('provider5')->nullable();
            $table->string('provider5_email')->nullable();
            $table->string('status')->nullable();
            $table->string('update')->nullable();
            $table->date('data_i')->nullable();
            $table->date('data_f')->nullable();
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
        Schema::dropIfExists('purchase_quotation');
    }
}
