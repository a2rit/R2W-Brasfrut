<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseRequestItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_request_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('idPurchaseRequest');
            $table->foreign('idPurchaseRequest')->references("id")->on("purchase_requests")->onDelete("cascade");
            $table->string('itemCode',15);
            $table->double('quantity');
            $table->string('project')->nullable();
            $table->string('distrRule')->nullable();
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
        Schema::dropIfExists('purchase_request_items');
    }
}
