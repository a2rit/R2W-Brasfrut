<?php

namespace App;

use Litiano\Sap\Company;
use Illuminate\Database\Eloquent\Model;

/**
 * App\ConfigSAP
 *
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ConfigSAP newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ConfigSAP newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ConfigSAP query()
 */
class ConfigSAP extends Model
{
    public function getLabelUtilization($id){
        $sap = new Company(false);
        return $sap->getDb()->table('OUSG')->where('ID','=', $id)->get(['Descr as name'])[0]->name;
    }
    public function getLabelProject($id){
        $sap = new Company(false);
        return $sap->getDb()->table('OPRJ')->where('PrjCode','=', $id)->get(['PrjName as name'])[0]->name;
    }
    public function getLabelDistributionRule($id){
        $sap = new Company(false);
        return $sap->getDb()->table('OOCR')->where('OcrCode','=', $id)->get(['OcrName as name'])[0]->name;

    }
    public function getLabelCFOP($id){
        $sap = new Company(false);
        return $sap->getDb()->table('OCFP')->where('Code','=', $id)->get(['Descrip as name'])[0]->name;

    }
}
