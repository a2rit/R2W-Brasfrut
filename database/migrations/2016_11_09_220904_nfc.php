<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Nfc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('nfc', function (Blueprint $table){
            $table->increments('id');
            $table->string("id_colibri")->nullable();
            $table->unsignedInteger('pv_id')->nullable();
            $table->foreign('pv_id')->references('id')->on('ponto_venda')->onDelete('set null');
            $table->dateTime('data_emissao');
            $table->unsignedInteger('numero');
            $table->unsignedInteger('serie');
            $table->string('versao');
            $table->string('chave')->unique();
            $table->string('natureza_operacao');
            $table->decimal('subtotal');
            $table->decimal('desconto');
            $table->decimal('servicos')->nullable();
            $table->decimal('total');
            $table->string('info_adicional');
            $table->unsignedInteger('codigo_sap')->nullable();
            $table->unsignedInteger('conta_receber')->nullable();
            $table->unsignedInteger('erro_id')->nullable();
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
        \Schema::dropIfExists('nfc');
    }
}
