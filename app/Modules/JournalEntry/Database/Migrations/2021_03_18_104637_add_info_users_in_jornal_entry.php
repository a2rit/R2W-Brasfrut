<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInfoUsersInJornalEntry extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('journal_entries', function (Blueprint $table) {
          $table->text('nameUser')->nullable();
          $table->text('idCancel')->nullable();
          $table->text('nameCancel')->nullable();
        });
    
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('journal_entries', function (Blueprint $table) {
          $table->dropColumn('nameUser');
          $table->dropColumn('idCancel');
          $table->dropColumn('nameCancel');
        });
    }
}
