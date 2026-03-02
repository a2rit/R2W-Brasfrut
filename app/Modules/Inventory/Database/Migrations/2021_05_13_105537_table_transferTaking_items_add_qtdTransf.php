<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableTransferTakingItemsAddqtdTransf extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::table('transferTaking_items', function (Blueprint $table) {
            $table->string('quantityTransfer', 10)->nullable();
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */

    public function down()
    {
        Schema::table('transferTaking_items', function (Blueprint $table) {
            
            $table->dropColumn('quantityTransfer');
        });
       
    }
}
