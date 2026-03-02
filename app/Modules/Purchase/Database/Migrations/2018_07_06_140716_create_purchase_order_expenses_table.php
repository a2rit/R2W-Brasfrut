<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseOrderExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_order_expenses', function (Blueprint $table) {
          $table->increments('id');
          $table->string('idPurchaseOrder', 25);
          $table->bigInteger('expenseCode');
          $table->text('tax');
          $table->double('lineTotal', 15);
          $table->string('project', 20);
          $table->string('distributionRule', 8);
          $table->text('comments')->nullable();
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
        Schema::dropIfExists('purchase_order_expenses');
    }
}
