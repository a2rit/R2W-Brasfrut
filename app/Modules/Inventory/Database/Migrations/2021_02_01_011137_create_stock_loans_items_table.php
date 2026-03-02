<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockLoansItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_loans_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idStockLoan', 20);
            $table->string('itemCode', 20);
            $table->string('quantity', 10);
            $table->string('projectCode', 8);
            $table->string('distributionRule', 8);
            $table->text('costCenter')->nullable();
            $table->text('costCenter2')->nullable();
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
        Schema::dropIfExists('stock_loans_items');
    }
}
