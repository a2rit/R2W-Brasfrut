<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsAdvancePaymentIncoingInvoice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('incoing_invoice_advance_payments', function (Blueprint $table) {
            $table->string('Comments', 254)->nullable();
            $table->date('DocDate')->nullable();
            $table->float('DocTotal')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('incoing_invoice_advance_payments', function (Blueprint $table) {
            $table->dropColumn('Comments');
            $table->dropColumn('DocDate');
            $table->dropColumn('DocTotal');
        });
    }
}
