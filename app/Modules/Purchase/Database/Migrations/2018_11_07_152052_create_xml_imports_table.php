<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXmlImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xml_imports', function (Blueprint $table) {
            $table->increments('id');
            $table->string("idUser",20);
            $table->text('chNFe');
            $table->date('taxDate')->nullable();
            $table->string('nNF')->nullable();
            $table->string('serie')->nullable();
            $table->text('comments')->nullable();
            $table->double('docTotal')->default(0);
            $table->double('totalFrete')->nullable();
            $table->double('totalDesconto')->nullable();
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
        Schema::dropIfExists('xml_imports');
    }
}
