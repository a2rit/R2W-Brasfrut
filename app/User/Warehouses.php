<?php

namespace App\User;

use Illuminate\Database\Eloquent\Model;

class Warehouses extends Model
{
    protected $table = "user_warehouses";
    protected $fillable = ["user_id", "whsCode"];
}
