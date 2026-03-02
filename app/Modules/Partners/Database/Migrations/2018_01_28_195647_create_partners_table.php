<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->nullable();
            $table->string('idUser')->nullable();
            $table->string('name');
            $table->string('fantasy_name')->nullable();
            $table->integer('type');
            $table->integer('group');
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('cpf')->nullable();
            $table->string('cnpj')->nullable();
            $table->string('ie')->nullable();
            $table->string('ie_st')->nullable();
            $table->string('im')->nullable();
            $table->text('comments')->nullable();
            $table->text('message')->nullable();
            $table->boolean('is_locked')->default(0)->nullable();
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
        Schema::dropIfExists('partners');
    }
}
