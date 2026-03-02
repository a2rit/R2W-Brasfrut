<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codSAP', 15);
            $table->string('idRequest', 15);
            $table->string('quantityRequest', 5);
            $table->string('quantityServed', 5)->nullable();
            $table->string('pendingAmount', 5)->nullable();
            $table->text('costCenter');
            $table->text('costCenter2')->nullable();
            $table->text('project');
            $table->text('status')->nullable();
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
        Schema::dropIfExists('request_products');
    }
}
