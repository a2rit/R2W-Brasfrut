<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTypeColumnsPartnerBankaccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partners_bankaccounts', function (Blueprint $table) {
            $table->string("Branch", 10)->change();
            $table->string("City", 10)->change();
            $table->string("Account", 10)->change();
            $table->string("ControlKey", 20)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partners_bankaccounts', function (Blueprint $table) {
            //
        });
    }
}
