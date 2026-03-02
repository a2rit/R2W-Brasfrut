<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDestWhsCodeInternConsumption extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('intern_consumption', function (Blueprint $table) {
            $table->string("dest_whs_code", 5)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('intern_consumption', function (Blueprint $table) {
            $table->dropColumn('dest_whs_code');
        });
    }
}
