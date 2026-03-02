<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXmlReceiptGoodItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xml_receipt_good_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idReceiptGoods',10);
            $table->string('itemCode',20)->nullable();
            $table->string('idItemXML',20);
            $table->double('quantity');
            $table->double('price');
            $table->double('lineSum');
            $table->string('codUse',10);
            $table->string('codProject',10);
            $table->string('codCost',10);
            $table->text('codCFOP',6)->nullable();
            $table->string('status')->defaut('1');
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
        Schema::dropIfExists('xml_receipt_googd_items');
    }
}
