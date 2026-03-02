<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsAdvanceProvider extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('advance_provider', function (Blueprint $table) {
            $table->string('message', 255)->default('');
            $table->integer('is_locked')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('advance_provider', function (Blueprint $table) {
            $table->dropColumn('message');
            $table->dropColumn('is_locked');
        });
    }
}
