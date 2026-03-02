<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContigenciaPath extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("ponto_venda", function (Blueprint $table){
            $table->string("pasta_xml_contingencia")->nullable();
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
            $table->removeColumn("pasta_xml_contingencia");
        });
    }
}
