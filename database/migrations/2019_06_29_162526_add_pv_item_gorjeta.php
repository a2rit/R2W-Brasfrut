<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPvItemGorjeta extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("ponto_venda", function (Blueprint $table){
            $table->string("item_gorjeta_colibri")->nullable();
            $table->string("item_gorjeta_sap")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("ponto_venda", function (Blueprint $table){
            $table->dropColumn("item_gorjeta_colibri");
            $table->dropColumn("item_gorjeta_sap");
        });
    }
}
