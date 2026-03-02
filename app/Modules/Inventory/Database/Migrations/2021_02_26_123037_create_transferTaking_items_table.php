<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransferTakingItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transferTaking_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idTransferTaking', 20);
            $table->string('itemCode', 20);
            $table->string('quantity', 10);
            $table->string('projectCode');
            $table->string('distributionRule');
            $table->text('costCenter')->nullable();
            $table->text('costCenter2')->nullable();
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
        Schema::dropIfExists('transferTaking_items');
    }
}
