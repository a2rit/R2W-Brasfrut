<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXmlItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xml_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idImportXML');
            $table->string('itemCode')->nullable();
            $table->string('codPartners')->nullable();
            $table->string('EAN')->nullable();
            $table->string('name')->nullable();
            $table->string('uCom')->nullable();
            $table->double('qCom')->nullable();
            $table->double('vUnCom')->nullable();
            $table->double('vProd')->nullable();
            $table->string('CFOP')->nullable();
            $table->string('NCM')->nullable();
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
        Schema::dropIfExists('xml_items');
    }
}
