<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\cashFlowItems
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $idUser
 * @property string $idCashFlow
 * @property string $idTransation
 * @property string $transation
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CFItems whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CFItems whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CFItems whereIdCashFlow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CFItems whereIdTransation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CFItems whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CFItems whereTransation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CFItems whereUpdatedAt($value)
 * @property float|null $docTotal
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CFItems newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CFItems newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CFItems query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CFItems whereDocTotal($value)
 */
class CFItems extends Model
{
    protected $table = 'cash_flow_items';

    public function saveInDB($idCashflow,$idTransation,$transation, $docTotal = NULL){
        $this->idUser = auth()->user()->id;
        $this->idCashFlow = $idCashflow;
        $this->idTransation =  $idTransation;
        $this->transation =  $transation;
        $this->docTotal =  $docTotal;
        $this->save();
    }
}
