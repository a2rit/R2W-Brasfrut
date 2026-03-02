<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRequesterInTablePurchaseOrderRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->string('requesterUser', 15)->nullable();
            $table->string('clerkUser', 15)->nullable();
            $table->integer('whs')->nullable();
            $table->string('codStatus', 1)->nullable();
            $table->boolean('is_locked')->defaut('0')->nullable();
            $table->string('message')->nullable();
        });
       
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn('requesterUser');
            $table->dropColumn('clerkUser');
            $table->dropColumn('whs');
            $table->dropColumn('codStatus');
            $table->dropColumn('is_locked');
            $table->dropColumn('message');
        });
    
       
    }
}
