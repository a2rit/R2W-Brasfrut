<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBankNameColumnPartnersBankaccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partners_bankaccounts', function (Blueprint $table) {
            $table->string('BankName', 80)->nullable();
            $table->integer('City')->nullable()->change();
            $table->integer('Branch')->nullable()->change();
            $table->integer('ControlKey')->nullable()->change();
            $table->integer('BankKey')->nullable()->change();
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
            $table->dropColumn('Branch');
            $table->dropColumn('City');
            $table->dropColumn('ControlKey');
            $table->dropColumn('BankKey');
            $table->dropColumn('BankName');
        });
    }
}
