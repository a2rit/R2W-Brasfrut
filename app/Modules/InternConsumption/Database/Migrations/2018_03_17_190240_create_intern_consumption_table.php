<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInternConsumptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('intern_consumption', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('creator_user_id');
            $table->foreign('creator_user_id')->references('id')->on('users')->onDelete('no action');
            $table->unsignedInteger('authorizer_user_id')->nullable();
            $table->foreign('authorizer_user_id')->references('id')->on('users')->onDelete('no action');
            $table->dateTime('definition_date')->nullable();
            $table->integer('requester_sap_id');
            $table->string('requester_name');
            $table->string('requester_branch')->nullable();
            $table->date('date');
            $table->string('distribution_rule')->nullable();
            $table->string('distribution_rule2')->nullable();
            $table->string('project')->nullable();
            $table->string('status');
            $table->string('comment')->nullable();
            $table->string('observation')->nullable();
            $table->unsignedInteger('pos_id'); // Point of sale
            $table->integer('sales_order_code')->nullable();
            $table->integer('stock_transfer_code')->nullable();
            $table->string('message')->nullable();
            $table->string('delivery_location')->nullable();
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
        Schema::dropIfExists('intern_consumption');
    }
}
