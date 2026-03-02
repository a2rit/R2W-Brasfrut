<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNfcPagamentos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('nfc_pagamentos', function (Blueprint $table){
            $table->increments('id');
            $table->unsignedInteger('nfc_id');
            $table->foreign('nfc_id')->references('id')->on('nfc')->onDelete('cascade');
            $table->string('tipo');
            $table->decimal('valor');
            $table->string('numero_autorizacao')->nullable();
            $table->string('bandeira')->nullable();
            $table->string('cnpj_credenciadora')->nullable();
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
        \Schema::dropIfExists('nfc_itens');
    }
}
