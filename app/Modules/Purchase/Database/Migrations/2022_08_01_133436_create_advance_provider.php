<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdvanceProvider extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advance_provider', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('codSAP');
            $table->string('cardCode', 15);
            $table->date('docDate');
            $table->date('docDueDate');
            $table->date('taxDate');
            $table->integer('dpmPrcnt');
            $table->integer('idUser');
            $table->string('comments', 254);
            $table->string('paymentCondition');
            $table->string('paymentForm');
            $table->integer('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advance_provider');
    }
}
