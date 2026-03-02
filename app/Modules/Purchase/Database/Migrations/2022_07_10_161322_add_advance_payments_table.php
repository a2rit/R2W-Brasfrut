<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdvancePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incoing_invoice_advance_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('idIncoingInvoice');
            $table->integer('codSAP');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('incoing_invoice_advance_payments');
    }
}
