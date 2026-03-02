<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInputsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inputs', function (Blueprint $table) {
            $table->increments('id');
            $table->text('code');
            $table->text('codSAP')->nullable();
            $table->string('idUser',15);
            $table->string('DocDate', 35);
            $table->string('TaxDate', 35);
            $table->text('description')->nullable();
            $table->boolean('is_locked')->defaut('0');
            $table->string('message')->nullable();
            $table->string('dbUpdate')->nullable()->after('description');
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
        Schema::dropIfExists('inputs');
    }
}
