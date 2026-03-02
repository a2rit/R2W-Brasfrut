<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableStockLoansAddDevolution extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
     
        Schema::table('stock_loans', function (Blueprint $table) {
            $table->boolean('devolution')->default('0');
            $table->string('id_stockLoan',20)->nullable();
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
            $table->dropColumn('devolution');
            $table->dropColumn('id_stockLoan');
        });
    }
}
