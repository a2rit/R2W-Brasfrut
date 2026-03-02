<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

//Necessário mudança em quantidade de caracteres das colunas 
class ModifyStockLoansItemsParcialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
        Schema::table('stock_loans_items', function (Blueprint $table) {  
            $table->string('parcial')->nullable();
           
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_loans_items', function (Blueprint $table) {
            $table->dropColumn('parcial');
        });
    }
}
