<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idPurchaseOrders',10);
            $table->string('itemCode',20);
            $table->text('itemName')->nullable();
            $table->double('quantity');
            $table->double('price');
            $table->double('lineSum');
            $table->string('codUse',10);
            $table->string('codProject',10);
            $table->string('codCost',10);
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
        Schema::dropIfExists('purchase_order_items');
    }
}
