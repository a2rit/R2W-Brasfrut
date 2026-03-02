<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePorcionamentos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('porcionamentos', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->unsignedInteger("user_id")->nullable();
            $table->foreign("user_id")->references("id")->on("users")->onDelete("set null");
            $table->string("cod_item");
            $table->string("nome_item");
            $table->decimal("quantidade", 12, 3);
            $table->string("cod_fornecedor");
            $table->string("nome_fornecedor");
            $table->string("nota_fiscal");
            $table->unsignedInteger("cod_entrada")->nullable();
            $table->unsignedInteger("cod_saida")->nullable();
            $table->string("deposito");
            $table->string("unidade_medida");
            $table->decimal("preco");
            $table->unsignedInteger("documento_id");
            $table->integer("linha");
            $table->string("projeto");
            $table->string("regra_distribuicao");
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
        \Schema::dropIfExists("porcionamentos");
    }
}
