<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idUser',10);
            $table->string('docDate', 35);
            $table->text('taxDate');
            $table->string('code',10);
            $table->string('codSAP',25)->nullable();
            $table->string('fromWarehouse', 35);
            $table->string('toWarehouse', 35);
            $table->text('comments')->nullable();
            $table->boolean('is_locked')->defaut('0');
            $table->string('message')->nullable();
            $table->string('dbUpdate')->nullable();
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
        Schema::dropIfExists('transfers');
    }
}
