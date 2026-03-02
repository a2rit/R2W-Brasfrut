<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableStockLoansAddRequester extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
     
        Schema::table('stock_loans', function (Blueprint $table) {
            $table->string('requester')->nullable();
            $table->string('returner')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      
        Schema::table('stock_loans', function (Blueprint $table) {
            $table->dropColumn('requester');
            $table->dropColumn('returner');
        });
    }
}
