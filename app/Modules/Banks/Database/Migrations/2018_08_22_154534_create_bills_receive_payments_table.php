<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillsReceivePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bills_receive_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idBillsReceive',25);
            $table->double('docTotal')->nullable();
            $table->char('money')->default('N');
            $table->string('cashAccount',15)->nullable();
            $table->double('cashSum')->nullable();
            $table->char('credit')->default('N');
            $table->string('creditCard')->nullable();
            $table->date('cardValidUntil')->nullable();
            $table->string('creditCardNumber',20)->nullable();
            $table->bigInteger('numOfCreditPayments')->nullable();
            $table->string('creditAcct',15)->nullable();
            $table->double('creditSum')->nullable();
            $table->char('transfer')->default('N');
            $table->date('transferDate')->nullable();
            $table->string('transferAccount')->nullable();
            $table->double('transferSum')->nullable();
            $table->string('transferReference',27)->nullable();
            $table->char('other')->default('N');
            $table->string('otherAccount',15)->nullable();
            $table->double('otherSum')->nullable();
            $table->char('check')->default('N');
            $table->double('checkSum',15)->nullable();
            $table->date('dueDate')->nullable();
            $table->string('countryCode',3)->nullable();
            $table->string('bankCode',30)->nullable();
            $table->string('branch',50)->nullable();
            $table->string('acctNum',50)->nullable();
            $table->string('checkAccount',15)->nullable();
            $table->char('trnsfrable')->default('N');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bills_receive_payments');
    }
}
