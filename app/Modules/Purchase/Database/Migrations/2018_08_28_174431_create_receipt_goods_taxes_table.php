<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReceiptGoodsTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receipt_goods_taxes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idReceiptGoods',10);
            $table->bigInteger('seqCode')->nullable();
            $table->bigInteger('sequenceSerial')->nullable();
            $table->string('seriesStr',3)->nullable();
            $table->string('subStr',3)->nullable();
            $table->string('sequenceModel',3)->nullable();
            $table->string('NFEKey',44)->nullable();
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
        Schema::dropIfExists('receipt_goods_taxes');
    }
}
