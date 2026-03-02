<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWithheldTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withheld_taxes', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->bigInteger('itemId');
            $table->string('WTCode');
            $table->string('WTName');
            $table->string('Rate');
            $table->string('Category');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('withheld_taxes');
    }
}
