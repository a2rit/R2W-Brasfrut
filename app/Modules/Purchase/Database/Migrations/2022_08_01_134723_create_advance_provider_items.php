<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdvanceProviderItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advance_provider_items', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('idAdvanceProvider');
            $table->string('itemCode', 20);
            $table->float('quantity', 10, 3);
            $table->float('price', 10, 4);
            $table->string('project', 60);
            $table->string('distrRule', 10);
            $table->string('distrRule2', 10);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advance_provider_items');
    }
}
