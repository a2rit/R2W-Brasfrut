<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPermissionsUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::table("users", function (Blueprint $table){
            $table->json('permissions')->nullable();
        });
        $users = \App\User::all();
        foreach ($users as $user)
        {
            $user->permissions = '{"configuration":true,"config_boot":true,"config_sale_point":true,"config_users":true,"config_approvers":true,"config_users_group":true,"config_whs_group":true,"erros":true,"nfcs":true,"portioning":true,"portion_search":true,"portion_list":true,"portion_loss":false,"portion_justify":false,"portion_loss_justify":true,"intern_consumption":true,"inventoryx":true,"inventory_request":false,"inventory_input":false,"inventory_output":false,"inventory_transfer_taking":false,"inventory_transfer":true,"inventory_stock_loan":true,"accounting":true,"account_lcm":true,"b_partners":true,"b_partner":true,"purchasex":true,"purchase_order":true,"purchase_request":true,"purchase_nfc":true}';
            $user->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::table("users", function (Blueprint $table){
            $table->removeColumn("permissions");
        });
    }
}
