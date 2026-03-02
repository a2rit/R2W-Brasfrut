<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddimpostosInTableInvoice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('incoing_invoices', function (Blueprint $table) {
            $table->double('impostos_r')->nullable();
            $table->double('total_a_pagar')->nullable();
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
            $table->dropColumn('impostos_r');
            $table->dropColumn('total_a_pagar');
        });
    }
}
