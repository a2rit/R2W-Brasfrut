<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSupplierCatalogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_catalogs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('itemCode', 20);
            $table->string('cardCode', 15);
            $table->string('substitute', 20);
            $table->boolean('inSAP')->default('0');
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
        Schema::dropIfExists('supplier_catalogs');
    }
}
