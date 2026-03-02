<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateColibriNfc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('colibri_nfc', function (Blueprint $table) {
            $table->string("colibri_id")->primary();
            $table->string("chave_nfce")->unique();
            $table->dateTime("emissao");
            $table->decimal("valor");
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
        \Schema::dropIfExists("colibri_nfc");
    }
}
