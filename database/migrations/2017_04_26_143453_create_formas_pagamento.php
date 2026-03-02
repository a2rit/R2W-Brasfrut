<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormasPagamento extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('formas_pagamento', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pv_id');
            $table->foreign('pv_id')->references('id')->on('ponto_venda')->onDelete('cascade');
            $table->string("chave_colibri");
            $table->string("codigo_unico")->unique();
            $table->string("valor");
            $table->timestamps();
        });

        \Schema::table("ponto_venda", function (Blueprint $table){
            /**
             * Remove as colunas antigas.
             */
            $colunas = ["amex", "cred_card", "rede_shopp", "visa", "visa_electron", "outro_cartao"];
            foreach ($colunas as $coluna)
            {
                if(\Schema::hasColumn("ponto_venda", $coluna))
                {
                    $table->dropColumn($coluna);
                }
            }

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::dropIfExists("formas_pagamento");
    }
}
