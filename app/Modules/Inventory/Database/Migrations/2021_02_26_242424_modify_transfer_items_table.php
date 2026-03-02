<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

//Necessário mudança em quantidade de caracteres das colunas 
class ModifyTransferItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
        Schema::table('transfer_items', function (Blueprint $table) {  
            $table->string('projectCode')->change();
            $table->string('distributionRule')->change();
           
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transfer_items', function (Blueprint $table) {
            $table->string('projectCode', 8)->change();
            $table->string('distributionRule', 8)->change();
        });
    }
}
