<?php

namespace App\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Settings\Models\Config_date
 *
 * @property int $id
 * @property string $idUser
 * @property string $codSAP
 * @property string $description
 * @property int $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Config_date newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Config_date newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Config_date query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Config_date whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Config_date whereCodSAP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Config_date whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Config_date whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Config_date whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Config_date whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Config_date whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Config_date extends Model
{
    protected $filltable = ['idUser','codSAP','description','amount'];
    public function saveInDB($request){
        $this->idUser = $request['idUser'];
        $this->codSAP = $request['codSAP'];
        $this->description = $request['description'];
        $this->amount = $request['amount'];
        $this->save();
    }
}
