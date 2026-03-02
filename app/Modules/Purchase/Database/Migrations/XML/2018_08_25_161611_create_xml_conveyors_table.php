<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXmlConveyorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xml_conveyors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idImportXML');
            $table->string('modFrete')->nullable();
            $table->string('CNPJ')->nullable();
            $table->string('name')->nullable();
            $table->string('IE')->nullable();
            $table->string('street')->nullable();
            $table->string('namber')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('volume')->nullable();
            $table->string('kind')->nullable();
            $table->string('netWeight')->nullable();
            $table->string('grossWeight')->nullable();
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
        Schema::dropIfExists('xml_conveyors');
    }
}
