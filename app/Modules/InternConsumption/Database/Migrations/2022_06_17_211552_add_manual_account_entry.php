<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddManualAccountEntry extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('intern_consumption', function (Blueprint $table) {
            $table->bigInteger('manual_account_entry_id')->nullable();
            $table->decimal('order_total', 14, 4)->nullable();
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
            $table->dropColumn('manual_account_entry_id');
            $table->dropColumn('order_total');
        });
    }
}
