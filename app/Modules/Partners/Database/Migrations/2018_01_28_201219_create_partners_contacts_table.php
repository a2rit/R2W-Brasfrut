<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartnersContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partners_contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('partner_id');
            $table->foreign('partner_id')->references('id')->on('partners')->onDelete('cascade');
            $table->integer('line')->nullable();
            $table->integer('internal_code')->nullable();
            $table->string('name');
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('delete')->default(0)->nullable();
            $table->timestamps();
            $table->unique(['partner_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partners_contacts');
    }
}
