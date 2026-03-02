<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDpmApplColumnIncoingInvoiceAdvancePayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('incoing_invoice_advance_payments', function (Blueprint $table) {
            $table->float('DpmAppl')->nullable();
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
            $table->dropColumn('DpmAppl');
        });
    }
}
