<?php

namespace App\Modules\JournalEntry\Models;

use App\Modules\JournalEntry\Models\JournalEntry\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Litiano\Sap\Company;
use Litiano\Sap\Enum\BoObjectTypes;
use Litiano\Sap\IdeHelper\ICompany;
use Litiano\Sap\IdeHelper\IJournalEntries;
use App\LogsError;
use Litiano\Sap\NewCompany;
/**
 * App\Modules\JournalEntry\Models\JournalEntry
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int|null $number
 * @property string|null $comments
 * @property string|null $project
 * @property string|null $distribution_rule
 * @property string|null $message
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry whereDistributionRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry whereDocDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry wherePostingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry whereProject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry whereUpdatedAt($value)
 * @property \Carbon\Carbon $doc_date
 * @property \Carbon\Carbon $due_date
 * @property \Carbon\Carbon $posting_date
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\JournalEntry\Models\JournalEntry\Item[] $items
 * @property bool|null $is_locked
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry whereIsLocked($value)
 * @property string $idUser
 * @property string|null $codSAP
 * @property string|null $code
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry whereCodSAP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry whereIdUser($value)
 * @property string|null $dbUpdate
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry whereDbUpdate($value)
 * @property string|null $currencyValue
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry whereCurrencyValue($value)
 * @property string|null $type
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry whereType($value)
 * @property string|null $coin
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry whereCoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry query()
 */
class JournalEntry extends Model
{
    protected $table = 'journal_entries';

    protected $fillable = ['posting_date', 'idUser','nameUser','idCancel','nameCancel','code', 'doc_date', 'due_date', 'distribution_rule','costCenter','costCenter2', 'project', 'comments', 'dbUpdate', 'currencyValue','type','coin'];

    protected $dates = ['posting_date', 'doc_date', 'due_date'];

    public function items()
    {
        return $this->hasMany(Item::class, 'je_id', 'id');
    }

    /**
     * @param Company $sap
     * @return bool
     * @throws \Exception
     */
    public function saveInSap($obj)
    {   
      try{
        /** @var ICompany $sap */
        $sap = NewCompany::getInstance()->getCompany();
        /** @var IJournalEntries $je */
        $je = $sap->GetBusinessObject(BoObjectTypes::oJournalEntries);
        $je->ReferenceDate = $obj->posting_date->format('Y-m-d');
        $je->DueDate = $obj->due_date->format('Y-m-d');
        $je->TaxDate = $obj->doc_date->format('Y-m-d');
        $je->Memo = (string)$obj->comments;

        //Campos fields não encontrados, verificar no SAP
        
        // $je->UserFields->fields->Item("U_R2W_CODE")->value =  $obj->code;
        // $je->UserFields->fields->Item("U_R2W_USERNAME")->value = $this->getNameUser($obj->idUser);
        if ($obj->project) {
            $je->ProjectCode = $obj->project;
        }
        $aux =0;
        foreach ($obj->items as $item) {
            $je->Lines->SetCurrentLine($aux);
            $je->Lines->Credit = (double)$item->credit;
            $je->Lines->Debit = (double)$item->debit;

            if(!is_null($item->account)){
              $je->Lines->AccountCode = $item->account;
            }else{
              $je->Lines->ShortName = $item->cardCode;
            }

            // if ($item->distribution_rule) {
            //     $je->Lines->DistributionRule = $item->distribution_rule;
            // }

            if ($item->costCenter) {
                $je->Lines->CostingCode = $item->costCenter;
            }
            if ($item->costCenter2) {
                $je->Lines->CostingCode2 = $item->costCenter2;
            }
            if ($item->project) {
                $je->Lines->ProjectCode = $item->project;
            }
            $aux++;
            $je->Lines->Add();
        }

        if ($je->Add() !== 0) {
            $obj->message =  $sap->GetLastErrorDescription();
            $obj->is_locked = true;
            $obj->dbUpdate = false;
            $obj->save();
            return true;
        }else{
          $obj->codSAP = $sap->GetNewObjectKey();
          $obj->is_locked = false;
          $obj->dbUpdate = false;
          $obj->message = 'Salvo com sucesso!';
          $obj->save();
        }
      } catch (\Exception $e) {
        $obj->is_locked = true;
        $obj->dbUpdate = false;
        $obj->message = $e->getMessage();
        $obj->save();
        $logsError =  new LogsError();
        $logsError->saveInDB('EX0d1', $e->getFile(). ' | '.$e->getLine(), $e->getMessage());
      }
    }

    public function cenceledInSAP($obj){
      try {
        $sap = NewCompany::getInstance()->getCompany();
        $oje = $sap->GetBusinessObject(BoObjectTypes::oJournalEntries);
        if(empty($obj->codSAP) || is_null($obj->codSAP)){
          $obj->is_locked = false;
          $obj->dbUpdate = false;
          $obj->codStatus = '4';
          $obj->idCancel = auth()->user()->id;
          $obj->nameCancel = auth()->user()->name;

          $obj->save();

        }else{
          if($oje->GetByKey((string) $obj->codSAP)){
            if($oje->Cancel === 0){
              $obj->is_locked = false;
              $obj->dbUpdate = false;
              $obj->codStatus = '4';
              $obj->save();
            }else{
              $obj->message = $sap->GetLastErrorDescription();
              $obj->is_locked = true;
              $obj->save();
            }
          }
        }

      } catch (\Exception $e) {
        $obj->is_locked = true;
        $obj->message = $e->getMessage();
        $obj->dbUpdate = true;
        $obj->save();
      }
    }

    public function getNameUser($id){
        return JournalEntry::join('users','users.id','=','journal_entries.idUser')
                ->where('users.id','=', $id)
                ->get(['users.name'])[0]->name;
    }

}
