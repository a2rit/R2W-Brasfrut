<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePorcionamentoItens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('porcionamento_itens', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger("porcionamento_id");
            $table->foreign("porcionamento_id")->references("id")->on("porcionamentos")->onDelete("cascade");
            $table->string("cod_item");
            $table->string("nome_item");
            $table->decimal("quantidade_produzida", 12, 3);
            $table->decimal("quantidade_gasta", 12, 3);
            $table->string("deposito");
            $table->string("tipo");
            $table->decimal("custo", 12, 4);
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
        \Schema::dropIfExists("porcionamento_itens");
    }
}
