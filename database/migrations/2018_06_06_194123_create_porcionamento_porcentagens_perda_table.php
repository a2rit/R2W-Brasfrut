<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePorcionamentoPorcentagensPerdaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('porcionamento_porcentagens_perda', function (Blueprint $table) {
            $table->string('codigo')->primary();
            $table->decimal('porcentagem_base');
            $table->decimal('porcentagem_aceita');
            $table->string('comentario')->nullable();
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
        Schema::dropIfExists('porcionamento_porcentagens_perda');
    }
}
