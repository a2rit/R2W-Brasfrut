<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('idSalesOrder');
            $table->integer('idNfcItem');
            $table->string('itemCode', 20);
            $table->string('itemName', 256);
            $table->string('unitMsr')->nullable();
            $table->decimal('quantity', 20, 3);
            $table->decimal('price', 20, 4);
            $table->decimal('discount', 20, 2)->default(0.00);
            $table->decimal('lineSum', 20, 2);
            $table->decimal('anotherValues', 20, 2)->nullable();
            $table->integer('usage');
            $table->integer('codProject');
            $table->string('taxCode', 20)->nullable();
            $table->string('cfop')->nullable();
            $table->string('ncm')->nullable();
            $table->decimal('cst_icms', 20, 2)->default(0.00);
            $table->string('warehouseCode', 3)->nullable();
            $table->string('costCenter')->nullable();
            $table->string('costCenter2')->nullable();
            $table->integer('lineNum')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_order_items');
    }
}
