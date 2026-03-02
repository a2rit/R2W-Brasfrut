<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsumoInternoItensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("consumo_interno_itens", function (Blueprint $table){
            $table->increments("id");
            $table->unsignedInteger("ci_id");
            $table->foreign("ci_id")->references("id")->on("consumo_interno")->onDelete("cascade");
            $table->decimal("qtd", 8, 3);
            $table->string("cod_sap");
            $table->string("descricao");
            $table->string("centro_custo");
            $table->string("projeto");
            $table->unsignedInteger("user_id")->nullable();
            $table->foreign("user_id")->references("id")->on("users")->onDelete("set null");
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("consumo_interno_itens");
    }
}
