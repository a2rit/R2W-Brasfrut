<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfirmacaoPorcionamento extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::table('porcionamentos', function (Blueprint $table) {
            $table->unsignedInteger("usuario_autorizador_id")->nullable();
            $table->foreign("usuario_autorizador_id")->references("id")->on("users");
            $table->dateTime("data_autorizacao")->nullable();
            $table->unsignedInteger("justificativa_id")->nullable();
            $table->foreign("justificativa_id")->references("id")->on("porcionamento_justificativas");
            $table->string("justificativa")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::table('porcionamentos', function (Blueprint $table) {
            $table->dropForeign(["usuario_autorizador_id"]);
            $table->dropForeign(["justificativa_id"]);
            $table->dropColumn("usuario_autorizador_id");
            $table->dropColumn("justificativa_id");
            $table->dropColumn("justificativa");
            $table->dropColumn("data_autorizacao");
        });
    }
}
