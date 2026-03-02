<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

//Necessário mudança em quantidade de caracteres das colunas 
class ModifyStockLoansQtdPItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
        Schema::table('stock_loans_items', function (Blueprint $table) {  
            $table->string('quantityPending', 10)->nullable();
           
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
            $table->dropColumn('quantityPending');
        });
    }
}
