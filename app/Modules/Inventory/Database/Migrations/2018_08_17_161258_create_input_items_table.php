<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInputItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('input_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('wareHouseCode',20)->nullable();
            $table->string('idInputs', 15);
            $table->string('itemCode', 15);
            $table->string('quantity', 15);
            $table->string('price', 15);
            $table->string('projectCode');
            $table->string('costingCode');
            $table->text('costCenter');
            $table->text('costCenter2')->nullable();
            $table->string('accountCode', 15);
            $table->text('status')->nullable();
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
        Schema::dropIfExists('input_items');
    }
}
