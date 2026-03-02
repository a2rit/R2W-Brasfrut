<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PontoVenda extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('ponto_venda', function (Blueprint $table){
            $table->increments('id');
            $table->string('nome');
            $table->string('vendedor');
            $table->string('cliente');
            $table->string('modelo_nf');
            $table->string('regra_distribuicao');
            $table->string('regra_distribuicao_ov');
            $table->string('codigo_imposto');
            $table->string('codigo_imposto_ov');
            $table->string('utilizacao');
            $table->string('pasta_xml');
            $table->string('projeto');
            $table->string('projeto_ov');
            $table->string("codigo_ov");
            $table->string("conta_dinheiro");
            $table->string("conta_cheque");
            $table->string("conta_troco");
            $table->string("conta_servico");
            $table->string("deposito");
            $table->string("deposito_servico");
            $table->string("grupo_servico");
            $table->integer("serie");
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
        \Schema::dropIfExists('ponto_venda');
    }
}
