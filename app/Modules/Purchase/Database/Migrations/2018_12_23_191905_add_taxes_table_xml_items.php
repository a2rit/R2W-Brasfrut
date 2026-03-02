<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTaxesTableXmlItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('xml_items', function (Blueprint $table) {
            $table->string('ICMS',6)->default('0.0000');
            $table->string('IPI',6)->default('0.0000');
            $table->string('PIS',6)->default('0.0000');
            $table->string('COFINS',6)->default('0.0000');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('xml_items', function (Blueprint $table) {
            $table->string('ICMS',6)->default('0.0000');
            $table->string('IPI',6)->default('0.0000');
            $table->string('PIS',6)->default('0.0000');
            $table->string('COFINS',6)->default('0.0000');
        });
    }
}
