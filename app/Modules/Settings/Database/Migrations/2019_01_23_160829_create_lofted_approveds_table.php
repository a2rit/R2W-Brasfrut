<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoftedApprovedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lofted_approveds', function (Blueprint $table) {
            $table->increments('id');
            $table->string('docNum');
            $table->string('docName');
            $table->string('idUser');
            $table->double('first');
            $table->double('last');
            $table->double('quantity');
            $table->string('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lofted_approveds');
    }
}
