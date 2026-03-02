<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableErros1Change extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erros', function (Blueprint $table) {
            $table->unsignedInteger('pv_id')->nullable();
            $table->date('doc_date')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erros', function (Blueprint $table) {
            $table->dropColumn('pv_id');
            $table->dropColumn('doc_date');
        });
    }
}
