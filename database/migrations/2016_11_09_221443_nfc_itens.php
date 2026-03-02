<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NfcItens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('nfc_itens', function (Blueprint $table){
            $table->increments('id');
            $table->unsignedInteger('nfc_id');
            $table->foreign('nfc_id')->references('id')->on('nfc')->onDelete('cascade');
            $table->string('codigo_pdv');
            $table->string('nome');
            $table->string('unidade_comercial');
            $table->decimal('quantidade');
            $table->decimal('valor_unitario');
            $table->decimal('desconto');
            $table->decimal('outros_valores');
            $table->unsignedInteger('cfop');
            $table->unsignedInteger('ncm');
            $table->string('tipo')->nullable(); // Se IP ou IV
            $table->string('codigo_sap')->nullable();
            $table->string('grupo')->nullable();
            $table->string('codigo_entrada_item')->nullable();
            $table->string('status_op')->nullable();
            $table->unsignedInteger('codigo_op')->nullable();
            $table->unsignedInteger('erro_id')->nullable();
            $table->string("cst_icms");
            $table->string("cst_pis");
            $table->string("cst_cofins");
            $table->string("cst_ipi");
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
