<?php

namespace App\User;

use Illuminate\Database\Eloquent\Model;

class CostCenter extends Model
{
    protected $table = 'user_cost_centers';
    protected $fillable = ['user_id', 'costCenterCode', 'costCenterName', 'costCenterCode2', 'costCenterCode2'];
}
