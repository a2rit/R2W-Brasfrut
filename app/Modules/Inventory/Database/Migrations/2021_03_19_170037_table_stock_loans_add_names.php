<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableStockLoansAddNames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_loans', function (Blueprint $table) {
            $table->string('nameDevolved')->nullable();
            $table->string('nameRequester')->nullable();
          
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
            $table->dropColumn('nameDevolved');
            $table->dropColumn('nameRequester');
        });
    }
}
