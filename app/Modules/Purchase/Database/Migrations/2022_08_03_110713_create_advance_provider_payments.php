<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdvanceProviderPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advance_provider_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('idAdvanceProvider');
            $table->string('money', 1)->nullable();
            $table->string('cashAccount', 50)->nullable();
            $table->float('cashSum', 20, 2)->nullable();
            $table->string('debit', 1)->nullable();
            $table->string('creditCard', 30)->nullable();
            $table->date('cardValidUntil')->nullable();
            $table->string('creditCardNumber', 255)->nullable();
            $table->integer('numOfCreditPayments')->nullable();
            $table->string('creditAcct', 20)->nullable();
            $table->float('creditSum', 20, 2)->nullable();
            $table->string('transfer', 255)->nullable();
            $table->date('transferDate')->nullable();
            $table->string('transferAccount', 255)->nullable();
            $table->float('transferSum', 20, 2)->nullable();
            $table->string('transferReference', 27)->nullable();
            $table->string('other', 255)->nullable();
            $table->string('otherAccount', 15)->nullable();
            $table->float('otherSum', 20, 2)->nullable();
            $table->string('check', 255)->nullable();
            $table->float('checkSum', 20, 2)->nullable();
            $table->date('dueDate')->nullable();
            $table->string('bankCode', 30)->nullable();
            $table->string('acctNum', 50)->nullable();
            $table->string('checkAccount', 15)->nullable();
            $table->string('trnsfrable', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advance_provider_payments');
    }
}
