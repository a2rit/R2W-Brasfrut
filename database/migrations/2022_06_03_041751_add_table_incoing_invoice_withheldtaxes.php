<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableIncoingInvoiceWithheldtaxes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incoing_invoice_withheldtaxes', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->bigInteger('itemId');
            $table->string('WTCode');
            $table->string('WTName');
            $table->string('Rate');
            $table->string('Value')->nullable();
            $table->string('Category');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('incoing_invoice_withheldtaxes');
    }
}
