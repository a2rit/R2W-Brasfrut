<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseQuotationItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_quotation_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('idPurchaseQuotation');
            $table->foreign('idPurchaseQuotation')->references("id")->on("purchase_quotation")->onDelete("cascade");
            $table->string('itemCode',15);
            $table->string('itemName')->nullable();
            $table->string('qtd');
            $table->string('priceP1')->nullable();
            $table->string('qtdP1')->nullable();
            $table->string('totalP1')->nullable();
            $table->string('priceP2')->nullable();
            $table->string('qtdP2')->nullable();
            $table->string('totalP2')->nullable();
            $table->string('priceP3')->nullable();
            $table->string('qtdP3')->nullable();
            $table->string('totalP3')->nullable();
            $table->string('priceP4')->nullable();
            $table->string('qtdP4')->nullable();
            $table->string('totalP4')->nullable();
            $table->string('priceP5')->nullable();
            $table->string('qtdP5')->nullable();
            $table->string('totalP5')->nullable();
            

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
        Schema::dropIfExists('purchase_quotation_items');
    }
}
