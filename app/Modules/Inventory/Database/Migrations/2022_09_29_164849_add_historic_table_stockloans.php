<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHistoricTableStockloans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_loans_historics', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('idStockLoan');
            $table->integer('deliveryUserId');
            $table->integer('receiverUserId');
            $table->integer('idItem');
            $table->float('quantityServed', 10, 3);
            $table->integer('isDevolution')->nullable();
            $table->integer('isReceipt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_loans_historics');
    }
}
