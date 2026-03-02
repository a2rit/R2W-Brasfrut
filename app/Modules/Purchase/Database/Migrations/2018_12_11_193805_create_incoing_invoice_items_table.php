<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIncoingInvoiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incoing_invoice_items', function (Blueprint $table) {
          $table->increments('id');
          $table->string('idIncoingInvoice',10);
          $table->string('itemCode',20);
          $table->text('itemName')->nullable();
          $table->double('quantity');
          $table->double('price');
          $table->double('lineSum');
          $table->string('codUse',10);
          $table->string('codProject',10);
          $table->string('codCost',10);
          $table->text('codCFOP',6)->nullable();
          $table->text('taxCode')->nullable();
          $table->string('status')->defaut('1');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('incoing_invoice_items');
    }
}
