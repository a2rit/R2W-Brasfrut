<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCostCenterIdToLoftedApproveds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lofted_approveds', function (Blueprint $table) {
            $table->string('cost_center_id')->default('1.0');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lofted_approveds', function (Blueprint $table) {
            $table->dropColumn('cost_center_id');
        });
    }
}
