<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAmountAndResidualamountColumnsPartnerContracts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partner_contracts', function (Blueprint $table) {
            $table->decimal('amount', 19, 2)->default(0.00);
            $table->decimal('residualAmount', 19, 2)->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partner_contracts', function (Blueprint $table) {
            $table->dropColumn('amount');
            $table->dropColumn('residualAmount');
        });
    }
}
