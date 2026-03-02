<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableItemsAddLot extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('input_items', function (Blueprint $table) {
            $table->string('lot')->nullable();
        });
        Schema::table('output_items', function (Blueprint $table) {
            $table->string('lot')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('input_items', function (Blueprint $table) {
            $table->dropColumn('lot');
        });
        Schema::table('output_items', function (Blueprint $table) {
            $table->dropColumn('lot');
        });
    }
}
