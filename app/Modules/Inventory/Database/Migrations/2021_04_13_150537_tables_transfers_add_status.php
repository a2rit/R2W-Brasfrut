<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TablesTransfersAddStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::table('transfersTaking', function (Blueprint $table) {
            $table->string('status')->nullable();
        });
        Schema::table('transfers', function (Blueprint $table) {
            $table->string('status')->nullable();
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
            $table->dropColumn('status');
        });
        Schema::table('transfers', function (Blueprint $table) {
            $table->dropColumn('status');
        });
       
    }
}
