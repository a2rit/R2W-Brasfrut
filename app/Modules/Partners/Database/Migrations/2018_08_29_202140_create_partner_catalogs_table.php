<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartnerCatalogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_catalogs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idUser');
            $table->string('idXMLItem')->nullable();
            $table->string('cardCode',15)->nullable();
            $table->string('cardName',100)->nullable();
            $table->string('itemCode',20)->nullable();
            $table->string('itemName',100)->nullable();
            $table->string('substitute',20)->nullable();
            $table->text('message')->nullable();
            $table->boolean('is_locked')->default(0);
            $table->boolean('dbUpdate')->default(0);
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
        Schema::dropIfExists('partner_catalogs');
    }
}
