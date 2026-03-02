<?php

namespace  App\Modules\Banks\Models\BillsPayAccount;

use Illuminate\Database\Eloquent\Model;

/**
 * App\BillPayAccountItems
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $idBillsPayAccount
 * @property string $acctCode
 * @property string $descrip
 * @property string $role
 * @property string $valor
 * @property string $project
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount\Items whereAcctCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount\Items whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount\Items whereDescrip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount\Items whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount\Items whereIdBillsPayAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount\Items whereProject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount\Items whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount\Items whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount\Items whereValor($value)
 * @property float $docTotal
 * @property string $profitCenter
 * @property string $projectCode
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount\Items whereDocTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount\Items whereProfitCenter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount\Items whereProjectCode($value)
 * @property string $accountCode
 * @property string $decription
 * @property float $sumPaid
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount\Items newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount\Items newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount\Items query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount\Items whereAccountCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount\Items whereDecription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount\Items whereSumPaid($value)
 */
class Items extends Model
{
    protected $table = 'bill_pay_account_items';

    public function saveInDB($value, $id){
      $this->idBillsPayAccount = $id;
      $this->accountCode = $value['accountCode'];
      $this->decription = $value['decription'];
      $this->sumPaid =  clearNumberDouble($value['sumPaid']);
      $this->projectCode =  $value['projectCode'];
      $this->profitCenter =  $value['profitCenter'];
      $this->save();
    }

}
