<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PartnersBankaccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partners_bankaccounts', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('BankCode');
            $table->integer('Branch');
            $table->integer('Account');
            $table->integer('City');
            $table->integer('ControlKey');
            $table->integer('BankKey');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partners_bankaccounts');
    }
}
