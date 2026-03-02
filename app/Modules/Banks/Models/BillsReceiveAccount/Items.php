<?php

namespace App\Modules\Banks\Models\BillsReceiveAccount;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Banks\Models\BillsReceiveAccount\Items
 *
 * @property int $id
 * @property string $idBillsPayAccount
 * @property string $acctCode
 * @property string $descrip
 * @property string $role
 * @property string $valor
 * @property string $project
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount\Items whereAcctCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount\Items whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount\Items whereDescrip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount\Items whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount\Items whereIdBillsPayAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount\Items whereProject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount\Items whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount\Items whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount\Items whereValor($value)
 * @mixin \Eloquent
 * @property string $idBillsReceiveAccount
 * @property float $docTotal
 * @property string $profitCenter
 * @property string $projectCode
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount\Items whereDocTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount\Items whereIdBillsReceiveAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount\Items whereProfitCenter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount\Items whereProjectCode($value)
 * @property string $accountCode
 * @property string $decription
 * @property float $sumPaid
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount\Items newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount\Items newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount\Items query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount\Items whereAccountCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount\Items whereDecription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount\Items whereSumPaid($value)
 */
class Items extends Model
{
      protected $table = 'bills_receive_account_items';

      public function saveInDB($value, $id){
        $this->idBillsReceiveAccount = $id;
        $this->accountCode = $value['accountCode'];
        $this->decription = $value['decription'];
        $this->sumPaid =  clearNumberDouble($value['sumPaid']);
        $this->projectCode =  $value['projectCode'];
        $this->profitCenter =  $value['profitCenter'];
        $this->save();
      }
}
