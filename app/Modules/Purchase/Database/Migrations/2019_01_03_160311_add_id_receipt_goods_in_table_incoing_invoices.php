<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIdReceiptGoodsInTableIncoingInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('incoing_invoices', function (Blueprint $table) {
            $table->string('idReceiptGoods',20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('incoing_invoices', function (Blueprint $table) {
            $table->string('idReceiptGoods',20)->nullable();
        });
    }
}
