<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJournalEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idUser');
            $table->string('codSAP')->nullable();
            $table->string('code')->nullable();
            $table->date('doc_date');
            $table->date('due_date');
            $table->date('posting_date');
            $table->string('comments')->nullable();
            $table->string('project')->nullable();
            $table->string('distribution_rule')->nullable();
            $table->boolean('is_locked')->default(0)->nullable();
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
        Schema::dropIfExists('journal_entries');
    }
}
