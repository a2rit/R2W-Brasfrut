<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRequesterInTablePurchaseOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->unsignedInteger('creator_user_id')->nullable();
            $table->foreign('creator_user_id')->references('id')->on('users')->onDelete('no action');
            $table->unsignedInteger('authorizer_user_id')->nullable();
            $table->foreign('authorizer_user_id')->references('id')->on('users')->onDelete('no action');
            $table->dateTime('definition_date')->nullable();
            $table->integer('requester_sap_id')->nullable();
            $table->string('requester_name')->nullable();
            $table->string('requester_branch')->nullable();
        });
       
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign('creator_user_id');
            $table->dropColumn('creator_user_id');
            $table->dropForeign('authorizer_user_id');
            $table->dropColumn('authorizer_user_id');
            $table->dropColumn('definition_date');
            $table->dropColumn('requester_sap_id');
            $table->dropColumn('requester_name');
            $table->dropColumn('requester_branch');
        });
    
       
    }
}
