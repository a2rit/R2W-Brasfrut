<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codSAP', 15)->nullable();
            $table->string('requesterUser', 15);
            $table->string('code', 15);
            $table->string('clerkUser', 15)->nullable();
            $table->string('requiredDate', 15);
            $table->string('documentDate', 15);
            $table->text('description')->nullable();
            $table->text('description2')->nullable();
            $table->string('codStatus', 1)->nullable();
            $table->boolean('is_locked')->defaut('0');
            $table->string('message')->nullable();
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
        Schema::dropIfExists('requests');
    }
}
