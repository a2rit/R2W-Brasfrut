<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillPayAccountItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bill_pay_account_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idBillsPayAccount',10);
            $table->string('accountCode',15);
            $table->string('decription',64);
            $table->double('sumPaid');
            $table->string('projectCode',8);
            $table->string('profitCenter',8);
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
        Schema::dropIfExists('bill_pay_account_items');
    }
}
