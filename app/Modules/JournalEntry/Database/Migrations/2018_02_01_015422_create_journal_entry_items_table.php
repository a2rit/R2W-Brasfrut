<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJournalEntryItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('journal_entry_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('je_id');
            $table->string('account')->nullable();
            $table->string('cardCode',15)->nullable();
            $table->decimal('credit')->nullable();
            $table->decimal('debit')->nullable();
            $table->string('project')->nullable();
            $table->string('distribution_rule')->nullable();
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
        Schema::dropIfExists('journal_entry_items');
    }
}
