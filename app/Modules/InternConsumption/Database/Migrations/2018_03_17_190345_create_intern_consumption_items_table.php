<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInternConsumptionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('intern_consumption_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('intern_consumption_id');
            $table->foreign('intern_consumption_id')->references('id')
                ->on('intern_consumption')->onDelete('cascade');
            $table->string('code');
            $table->string('name');
            $table->string('type');
            $table->decimal('qty', 8, 3);
            $table->decimal('value');
            $table->integer('production_order_code')->nullable();
            $table->integer('delivery_code')->nullable();
            $table->string('production_order_status')->nullable();
            $table->string('message')->nullable();
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
        Schema::dropIfExists('intern_consumption_items');
    }
}
