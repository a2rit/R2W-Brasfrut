<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeOfAddressOnPartnersAddresses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partners_addresses', function (Blueprint $table) {
            $table->string('typeofaddress')->nullable();
            $table->string('county')->nullable();
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
            $table->dropColumn('typeofaddress');
            $table->dropColumn('county');
        });
       
    }
}
