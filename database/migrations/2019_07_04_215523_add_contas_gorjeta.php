<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContasGorjeta extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("ponto_venda", function (Blueprint $table) {
            $table->string("conta_gorjeta_credito")->nullable();
            $table->string("conta_gorjeta_debito")->nullable();
        });

        Schema::table('nfc', function (Blueprint $table) {
            $table->unsignedInteger('lancamento_gorjeta')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("ponto_venda", function (Blueprint $table) {
            $table->dropColumn("conta_gorjeta_credito");
            $table->dropColumn("conta_gorjeta_debito");
        });

        Schema::table('nfc', function (Blueprint $table) {
            $table->dropColumn('lancamento_gorjeta');
        });
    }
}
