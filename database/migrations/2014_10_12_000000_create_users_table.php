<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean("xml_colibri_sap")->default(0);
            $table->boolean("porcionamento")->default(0);
            $table->boolean("admin")->default(0);
            $table->rememberToken();
            $table->timestamps();
        });

        $user = new \App\User();
        $user->email = 'r2w@a2r-it.com.br';
        $user->name = 'Admin';
        $user->password = bcrypt("a2r@5988");
        $user->admin = true;
     
        $user->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
