<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOutputItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('output_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idOutputs',10);
            $table->string('wareHouseCode',20)->nullable();
            $table->string('itemCode', 15);
            $table->string('quantity', 15);
            #$table->string('price', 15);
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
        Schema::dropIfExists('output_items');
    }
}
