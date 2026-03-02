<?php

namespace App\Modules\Partners\Models\Partner;

use App\LogsError;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Litiano\Sap\Company;
use Litiano\Sap\IdeHelper\IBusinessPartners;
use Litiano\Sap\NewCompany;

class Contract extends Model
{
    protected $fillable = ['partner_id', 'cardCode', 'code', 'contractNumber', 'startDate', 'endDate', 'amount', 'residualAmount'];
    protected $table = 'partner_contracts';


    public function saveInSap($sap, $partner)
    {
        $update = false;
        $contract = $sap->UserTables->Item("A2R_PNCONTRATOS");
        $contract->GetByKey((String)$this->code);

        if(strlen($contract->Code)){
            $update = true;
        }

        $contract->Code = (String)$this->code;
        $contract->Name = (String)$this->code;
        $contract->UserFields->Fields->Item("U_A2R_PNCONTRATO")->Value = $this->contractNumber;
        $contract->UserFields->Fields->Item("U_A2R_CODPN")->Value = $partner->code;
        $contract->UserFields->Fields->Item("U_A2R_DTINICIO")->Value = $this->startDate;
        $contract->UserFields->Fields->Item("U_A2R_DTFIM")->Value = $this->endDate;
        
        if($update){
            $ret = $contract->Update();
        }else{
            $ret = $contract->Add();
        }
        
        if ($ret !== 0) {
            $logsError = new logsError();
            $logsError->saveInDB('PNC0001', 'Erro ao salvar o contrato no SAP', $sap->GetLastErrorDescription());
            $partner->message = 'Erro ao salvar o contrato no SAP: '.$sap->GetLastErrorDescription();
            $partner->save();
        }
    }

    public function removeInSAP(String $contract_code)
    {
        $sap = NewCompany::getInstance()->getCompany();
        $contract = $sap->UserTables->Item("A2R_PNCONTRATOS");
        $contract->GetByKey((String)$contract_code);

        if($contract->Remove() === 0){
            Contract::where("code", "=", $contract_code)->delete();
        }else{
            throw new Exception("Error: {$sap->GetLastErrorCode()} - {$sap->GetLastErrorDescription()}", 1);
        }
        return !$contract->Remove();
    }

    public function createCode(){
        $busca = DB::select("select top 1 code from partner_contracts order by id desc");
        $codigo = '';
        if (empty($busca) || is_null($busca) || $busca == '') {
              $codigo = 'PNC00001';
        } else {
              $codigo = $busca[0]->code;
              $codigo++;
        }
        return $codigo;
    }

    public function updateResidualValue($currentDate){
        $differenceBetweenCurrentDate = differenceBetweenTwoDatesOutputDays($currentDate, $this->endDate); // 13
        $differenceBetweenContractDates = differenceBetweenTwoDatesOutputDays($this->startDate, $this->endDate); // 19
        $valueInstallment = ($differenceBetweenContractDates <= 0 || $differenceBetweenCurrentDate <= 0) ? 0 : $this->amount / $differenceBetweenContractDates;
        $this->residualAmount = round($valueInstallment * $differenceBetweenCurrentDate, 2);
        $this->save();
    }
}
