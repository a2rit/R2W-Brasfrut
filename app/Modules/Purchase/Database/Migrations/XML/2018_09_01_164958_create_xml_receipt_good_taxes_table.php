<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXmlReceiptGoodTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xml_receipt_good_taxes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idReceiptGoods',10);
            $table->bigInteger('seqCode')->nullable();
            $table->bigInteger('sequenceSerial')->nullable();
            $table->string('seriesStr',3);
            $table->string('subStr',3);
            $table->string('sequenceModel',3);
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
        Schema::dropIfExists('xml_receipt_googd_taxes');
    }
}
