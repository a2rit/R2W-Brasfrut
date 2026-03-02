<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIeOnPartnersAddresses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partners_addresses', function (Blueprint $table) {
            $table->string('U_SKILL_IE')->nullable();
            $table->string('U_SKILL_indIEDest')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partners_addresses', function (Blueprint $table) {
            $table->dropColumn('U_SKILL_IE');
            $table->dropColumn('U_SKILL_indIEDest');
        });
       
    }
}
