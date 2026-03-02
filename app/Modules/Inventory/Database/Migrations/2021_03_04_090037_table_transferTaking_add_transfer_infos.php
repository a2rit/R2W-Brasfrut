<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableTransferTakingAddTransferInfos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transfersTaking', function (Blueprint $table) {
            $table->text('codSAPTransf')->nullable();
            $table->text('codWEBTransf')->nullable();
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transfersTaking', function (Blueprint $table) {
            $table->dropColumn('codSAPTransf');
            $table->dropColumn('codWEBTransf');
        });
       
    }
}
