<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillsReceiveAccountItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bills_receive_account_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idBillsReceiveAccount',10);
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
        Schema::dropIfExists('bills_receive_account_items');
    }
}
