<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddErroId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('intern_consumption', function (Blueprint $table) {
            $table->unsignedInteger('erro_id')->nullable();
        });
        Schema::table('intern_consumption_items', function (Blueprint $table) {
            $table->unsignedInteger('erro_id')->nullable();
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
            $table->dropColumn('erro_id');
        });
        Schema::table('intern_consumption_items', function (Blueprint $table) {
            $table->dropColumn('erro_id');
        });
    }
}
