<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Erros extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('erros', function (Blueprint $table) {
            $table->increments('id');
            $table->string('model');
            $table->unsignedInteger('model_id');
            $table->text('mensagem');
            $table->boolean('lido')->default('0');
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
        \Schema::dropIfExists('erros');
    }
}
