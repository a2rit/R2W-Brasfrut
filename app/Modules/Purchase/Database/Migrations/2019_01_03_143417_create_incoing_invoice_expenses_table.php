<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIncoingInvoiceExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incoing_invoice_expenses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idIncoingInvoice',10);
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
        Schema::dropIfExists('incoing_invoice_expenses');
    }
}
