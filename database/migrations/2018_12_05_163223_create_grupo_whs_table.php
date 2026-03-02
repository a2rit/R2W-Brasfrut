<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGrupoWhsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grupo_whs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idUser');
            $table->string('whsCode');
            $table->string('whsName');
            $table->string('type');
            $table->boolean('status')->defaut(true);
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
        Schema::dropIfExists('grupo_whs');
    }
}
