<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterErrosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erros', function (Blueprint $table) {
            $table->dropIndex(['model_id']);

            $table->bigInteger('attempt')->nullable();
            $table->string('exception')->nullable();
            $table->string('exception_code')->nullable();
            $table->index(['model', 'model_id'])->unique();
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
            $table->index(['model_id']);

            $table->dropColumn('attempt');
            $table->dropColumn('exception');
            $table->dropColumn('exception_code');
            $table->dropIndex(['model', 'model_id']);
        });
    }
}
