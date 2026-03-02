<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChangeNameColumnsPartnerContracts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partner_contracts', function (Blueprint $table) {
            $table->renameColumn('contract_number', 'contractNumber');
            $table->renameColumn('start_date', 'startDate');
            $table->renameColumn('end_date', 'endDate');
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
            //
        });
    }
}
