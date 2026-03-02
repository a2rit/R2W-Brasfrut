<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillsReceiveInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bills_receive_invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idBillsReceive',25);
            $table->string('type',25);
            $table->bigInteger('docEntry');
            $table->bigInteger('docNum');
            $table->date('docDate');
            $table->date('dueDate');
            $table->string('serial',50)->nullable();
            $table->bigInteger('installmentId')->nullable();
            $table->string('parcel',20)->nullable();
            $table->double('lineSum');
            $table->text('description')->nullable();
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
        Schema::dropIfExists('bills_receive_invoices');
    }
}
