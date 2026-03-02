<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsumoInternoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("consumo_interno", function (Blueprint $table){
            $table->increments("id");
            $table->unsignedInteger("pv_id");
            $table->foreign("pv_id")->references("id")->on("ponto_venda")->onDelete("cascade");
            $table->date("data");
            $table->unique(["data", "pv_id"], "lancamento_diario");
            $table->unsignedInteger("cod_transferencia")->nullable();
            $table->unsignedInteger("cod_pedido")->nullable();
            $table->string("mensagem")->nullable();
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
        Schema::dropIfExists("consumo_interno");
    }
}
