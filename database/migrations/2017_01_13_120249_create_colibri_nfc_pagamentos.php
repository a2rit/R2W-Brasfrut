<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateColibriNfcPagamentos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('colibri_nfc_pagamentos', function (Blueprint $table) {
            $table->increments("id");
            $table->string("colibri_nfc_id");
            //$table->foreign("colibri_nfc_id")->references("colibri_id")->on("colibri_nfc")->onDelete("cascade");
            // Removi a chave, pois o arquivo de pagamento pode vim antes do arquivo principal. Add uma chave unica
            $table->string("codigo_unico")->unique();
            $table->string("descricao");
            $table->decimal("valor");
            $table->string("numero_autorizacao");
            /**
             * @TODO TERMINAR
             * Como saber se já foi processado e não repetir o processamento?
             * Utilizar o campo data como unico? uma transação não pode ocorrer no mesmo segundo.
             * Mas temos pontos de venda separados. Podendo acontecer!!!
             * Mas tb, os XMLs de cada ponto serão depositados em pastas separadas.
             *
             */
            $table->decimal("gorjeta")->nullable();
            $table->unsignedInteger('gorjeta_codigo_sap')->nullable();
            $table->unsignedInteger('pv_id')->nullable();
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
        \Schema::dropIfExists("colibri_nfc_pagamentos");
    }
}
