<?php

namespace App\Modules\Partners\Models;

use App\Jobs\LinkUploadsInDocument;
use App\Jobs\Queue;
use App\Modules\Partners\Models\Partner\Address;
use App\Modules\Partners\Models\Partner\Contact;
use App\Modules\Partners\Models\Partner\Payments;
use App\Modules\Partners\Models\Partner\BankAccounts;
use App\Modules\Partners\Models\Partner\Contract;
use App\Modules\Settings\Models\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Litiano\Sap\Company;
use Litiano\Sap\NewCompany;
use Litiano\Sap\Enum\BoObjectTypes;
use Litiano\Sap\IdeHelper\IBusinessPartners;
use App\LogsError;
use App\Upload;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Modules\Partners\Models\Partner
 *
 * @property int $id
 * @property string|null $code
 * @property string $name
 * @property string|null $fantasy_name
 * @property int $type
 * @property int $group
 * @property string|null $telephone
 * @property string|null $email
 * @property string $cpf_cnpj
 * @property string|null $ie
 * @property string|null $ie_st
 * @property string|null $im
 * @property string $comments
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner whereFantasyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner whereIe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner whereIeSt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner whereIm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $cpf
 * @property string|null $cnpj
 * @property bool|null $is_locked
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner whereCnpj($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner whereCpf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner whereIsLocked($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Partners\Models\Partner\Address[] $addresses
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Partners\Models\Partner\Contact[] $contacts
 * @property string|null $message
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner whereMessage($value)
 * @property-read Address $billing_address
 * @property string|null $idUser
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner whereIdUser($value)
 */
class Partner extends Model
{
    protected $fillable = ['code', 'name', 'fantasy_name', 'type', 'group', 'telephone','paymentForm','paymentTerms','priceList','juros','cnae','contaContabil','contaControle', 'bill_exchange_account_payable', 'assets_bill_exchange_account_payable',
        'email', 'cpf', 'cnpj', 'ie', 'ie_st', 'im', 'comments','idUser', 'default_bankcode', 'active'];
    protected $table = 'partners';
    
    
    public function uploads(): HasMany
    {
        return $this->hasMany(Upload::class, 'idReference', 'id')->where("reference", "=", $this->table);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'partner_id', 'id');
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'partner_id', 'id');
    }

    public function payments()
    {
        return $this->hasMany(Payments::class, 'partner_id', 'id');
    }

    public function bankaccounts()
    {
        return $this->hasMany(BankAccounts::class, 'partner_id', 'id');
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class, 'partner_id', 'id');
    }
    
    
    /**
     * @param Company $sap
     * @param $cnpj
     * @return $cardCode
     * @throws \Exception
     */
    public static function getCardCode(Company $sap, $cnpj){
      try {
        return $sap->query("SELECT distinct T0.CardCode FROM OCRD  T0
                                          INNER JOIN CRD7 T2 ON T0.CardCode = T2.CardCode
                                          WHERE T2.TaxId0 like '{$cnpj}' ")[0]['CardCode'];
      } catch (\Exception $e) {
        return false;
      }

    }
    /**
     * @param Company $sap
     * @param $cardCode
     * @param bool $forceReturn
     * @return Partner
     * @throws \Exception
     */
    public static function getUpdated(Company $sap, $cardCode, $forceReturn = false)
    {
       $query = "select top 1 OCRD.CardCode as code, CardName name, ValidFor as active, CardFName as fantasy_name, CardType as type,
                GroupCode as [group], GroupNum as paymentTerms, PymCode as paymentForm, Phone1 as telephone, E_Mail as email, Free_Text as comments, TaxId0 as cnpj,CNAEId as cnae,
                TaxId1 as ie, TaxId2 as ie_st, TaxId3 as im, TaxId4 as cpf,DebPayAcct as contaContabil,DpmClear as contaControle, BankCode as default_bankcode,
                case when Phone2 is not null then concat(Phone2, Phone1) else Phone1 end as telephone
                from OCRD 
                left join CRD7 on CRD7.CardCode = OCRD.CardCode where OCRD.CardCode = :cardCode";

        $queryBillExchange = "SELECT *  FROM CRD3 T0 WHERE T0.[CardCode] = :cardCode";

        $types = ['C' => 0, 'S' => 1, 'L' => 2];    
        $result = $sap->getDb()->selectOne($query, ['cardCode' => $cardCode]);
        $resultBillExchange = $sap->getDb()->select($queryBillExchange, ['cardCode' => $cardCode]);

        foreach($resultBillExchange as $key => $value){
            if($value->AcctType == 'P'){
                $result->bill_exchange_account_payable = $value->AcctCode;
            }else if($value->AcctType == 'Y'){
                $result->assets_bill_exchange_account_payable = $value->AcctCode;
            }
        }
        $result->type = $types[$result->type];

        $partner = Partner::where("code", "=", $cardCode)->first();

        if ($partner) {
            /*if ($partner->is_locked) {
                if($forceReturn) {
                    return $partner;
                }
                throw new \Exception('Parceiro de negócios aguardando sincronização com SAP!');
            }*/
            $partner->fill((array)$result);
            $partner->message = null;
            $partner->save();
        } else {
            $result->is_locked = false;
            $partner = Partner::create((array)$result);
        }

        $queryAddresses = "select Address as name, Street as street, StreetNo as number, Building as complement,U_SKILL_IE as U_SKILL_IE,U_SKILL_indIEDest as U_SKILL_indIEDest,AddrType as typeofaddress,
         Block as neighborhood, City as city, ZipCode as postcode, Country as country,County as county, State as state, AdresType as type, LineNum as line
          from CRD1 where CardCode = :cardCode order by line asc";

        //Busca os endereços salvos para o parceiro no SQL
        $oldAddress = $partner->addresses()->get();
        // dd($sap->getDb()->select($queryAddresses, ['cardCode' => $cardCode]));
        // dd($oldAddress);
        //Deleta os endereços salvos para o parceiro no SQL
        $partner->addresses()->delete();

        $addressTypes = ['S' => 0, 'B' => 1];
        // Set Line num manualmente, usar o da tabela não serve.
        $line = 0;
       
        // dd($oldAddress);
        //Processo que busca o endereço salvo no SQL, compara com o salvo no SAP e adiciona os campos novos, se não, somente salva o endereço do SAP no SQL.
        foreach ($sap->getDb()->select($queryAddresses, ['cardCode' => $cardCode]) as $address) {
        
            // if($oldAddress->isNotEmpty()){
            //     $address->type = (string)$addressTypes[$address->type];
            //     foreach($oldAddress as $value){
                    
            //         if($partner->compareAddress($value,$address)){
                        // $address->partner_id = $partner->id;
                        // $address->line = $line;
                        // //Campos novos adicionados
                        // $address->typeofaddress = $value->typeofaddress;
                        // $address->county = $value->county;
                        // $address->U_SKILL_IE = $value->U_SKILL_IE;
                        // $address->U_SKILL_indIEDest = $value->U_SKILL_indIEDest;
                        // // dd($value);
                        // $newAdd = Address::create((array)$address);
                        // // dd($newAdd);
                        // $line++;
            //         }
            //     }

            // }else{
                    $address->partner_id = $partner->id;
                    $address->type = $addressTypes[$address->type];
                    $address->line = $line;

                    Address::create((array)$address);
                    $line++;  
            // }
        }

        $partner->payments()->delete();
        $queryPayments = "SELECT * FROM CRD2  where CardCode = :cardCode";
        foreach ($sap->getDb()->select($queryPayments, ['cardCode' => $cardCode]) as $payments) {
            $payments->partner_id = $partner->id;
            $payments->description = $payments->PymCode;
            $payments->code = $payments->CardCode;
            $line++;
            Payments::create((array)$payments);
        }

        $partner->bankaccounts()->delete();
        $queryBankAccounts = "SELECT T0.[BankCode], T0.[Branch], T0.[City], T0.[Street], T0.[Account], T0.[ControlKey], T0.[BankKey], T1.[BankName]
                              FROM OCRB T0 
                              INNER JOIN ODSC T1 ON T0.[BankCode] = T1.[BankCode]
                              WHERE CardCode = :cardCode";
        foreach ($sap->getDb()->select($queryBankAccounts, ['cardCode' => $cardCode]) as $bankaccount) {
          
            $bankaccount->partner_id = $partner->id;
            $line++;
            BankAccounts::create((array)$bankaccount);
        }
        

        $queryContacts = "select Name as name, E_MailL as email, Cellolar as telephone, CntctCode as internal_code
         from OCPR where CardCode = :cardCode order by CntctCode asc";
        $partner->contacts()->delete();
        $line = 0;

        foreach ($sap->getDb()->select($queryContacts, ['cardCode' => $cardCode]) as $contact) {
            $contact->partner_id = $partner->id;
            $contact->line = $line;
            $line++;
            Contact::create((array)$contact);
        }

        return $partner;
    }

    public function compareAddress($addressSQL,$addressSAP){
        $address1 = $this->forgeAddress($addressSQL);
        $address2 = $this->forgeAddress($addressSAP);
        
        if($address1 == $address2){
            return true;
        }else{
            return false;
        }
        
    }

    public function forgeAddress($address){
        $newAddress['name'] = $address->name;
        $newAddress['street']= $address->street;
        $newAddress['number']= $address->number;
        $newAddress['complement']= $address->complement;
        $newAddress['neighborhood']= $address->neighborhood;
        $newAddress['city']= $address->city;
        $newAddress['postcode']= $address->postcode;
        $newAddress['country']= $address->country;
        $newAddress['county']= $address->county;
        $newAddress['state']= $address->state;
        $newAddress['type']= $address->type;
        $newAddress['line']= $address->line;

        return $newAddress;
    }

    public function getBillingAddressAttribute()
    {
        return $this->addresses()->where("type", "=", 1)->first();
    }

    /**
     * @param Company $sap
     * @throws \Exception
     */
    public function saveInSap()
    {
        $sap = NewCompany::getInstance()->getCompany();
        
        /** @var IBusinessPartners $partner */
        $partner = $sap->GetBusinessObject(BoObjectTypes::oBusinessPartners);
        $update = false;
        //dd($this);
        if ($this->code) {
            $partner->GetByKey((string)$this->code);
            $update = true;
        } elseif ((int)$this->type === 1) {
            $partner->Series = '116';
        } else {
            $partner->Series = (int)Config::get('bpc-series');
        }
        
        $partner->CardCode = (string)$this->code;
        $partner->CardName = (string)$this->name;
        $partner->CardForeignName = (string)$this->fantasy_name;
        $partner->CardType = (int)$this->type;
        $partner->GroupCode = (int)$this->group;
        $partner->DebitorAccount  = (string)$this->contaControle;
        $partner->DownPaymentClearAct = (string)$this->contaContabil;
    
        if((string)strlen($this->telephone) >= 10){
            $partner->Phone1 = (string)substr($this->telephone, 2);
            $partner->Phone2 = (string)str_split($this->telephone, 2)[0];
        }else{
            $partner->Phone1 = (string)$this->telephone;
        }

        $partner->EmailAddress = (string)$this->email;
        $partner->FiscalTaxID->TaxId0 = (string)$this->cnpj;
        $partner->FiscalTaxID->TaxId1 = (string)$this->ie;
        $partner->FiscalTaxID->TaxId2 = (string)$this->ie_st;
        $partner->FiscalTaxID->TaxId3 = (string)$this->im;
        $partner->FiscalTaxID->TaxId4 = (string)$this->cpf;
        $partner->FiscalTaxID->TaxId12 = (string)$this->code;
        
        //Adicionando o CNAE
        !empty($this->cnae) ? $partner->FiscalTaxID->CNAECode = (int)$this->cnae : null;
        
        $partner->FreeText = (string)$this->comments;
        $partner->PayTermsGrpCode = (int)$this->paymentTerms ?? '-1';
        // dd($partner->AccountRecivablePayables->SetCurrentLine(0), $partner->AccountRecivablePayables->AccountCode);
        $partner->AccountRecivablePayables->SetCurrentLine(0);
        $line = $partner->AccountRecivablePayables->AccountType === 4 ? 0 : 1;

        if(empty($this->code) || (!empty($this->code) && $this->code != 'F007958')){
            $partner->AccountRecivablePayables->SetCurrentLine(0);
            $line = $partner->AccountRecivablePayables->AccountType === 4 ? 0 : 1;
            if(isset($this->bill_exchange_account_payable) && !is_null($this->bill_exchange_account_payable)){
                if($update){
                    $partner->AccountRecivablePayables->SetCurrentLine($line);
                }
                $partner->AccountRecivablePayables->AccountCode = (string)$this->bill_exchange_account_payable;
                $partner->AccountRecivablePayables->AccountType = (int)4;
                $partner->AccountRecivablePayables->Add();
            }
            if(isset($this->assets_bill_exchange_account_payable) && !is_null($this->assets_bill_exchange_account_payable)){
                if($update){
                    $partner->AccountRecivablePayables->SetCurrentLine($line + 1);
                }
                $partner->AccountRecivablePayables->AccountCode = (string)$this->assets_bill_exchange_account_payable;
                $partner->AccountRecivablePayables->AccountType = (int)7;
                $partner->AccountRecivablePayables->Add();
            }
        }
        
        
        // $partner->PriceListNum = (int)$this->priceList;
        // $partner->IntrestRatePercent = (float)clearNumberDouble($this->juros);
      
        foreach ($this->payments()->get() as $key => $item) {
            
            $partner->BPPaymentMethods->PaymentMethodCode = (string)$item->code;
            $partner->BPPaymentMethods->Add();
        }
        
        $partner->PeymentMethodCode = (string)$this->paymentForm;
        
        /** @var Address $address */
        
        foreach ($this->addresses()->orderBy('line', 'asc')->get() as $address) {
            $address->saveInSap($partner);
        }

        /** @var Contact $contact */
        foreach ($this->contacts()->orderBy('line', 'asc')->get() as $contact) {
            $contact->saveInSap($partner);
        }

        /** @var BankAccounts $bankaccount */
        $cont = 0;
        
        foreach ($this->bankaccounts()->get() as $bankaccount) {
            $bankaccount->line = $cont;
            $bankaccount->saveInSap($partner);
            $cont++;
        }
        if($partner->BPBankAccounts->Count > $cont){
            for($x = $cont; $x < $partner->BPBankAccounts->Count; $x++){
              $partner->BPBankAccounts->SetCurrentLine($x);
              $partner->BPBankAccounts->Delete();
            }
        }

        //$sapUp = new Company(false);
        
        if ($update) {
            $ret = $partner->Update();

        // $sapUp->query("UPDATE OCRD SET PymCode='{$this->paymentForm}'  WHERE CardCode='{$this->code}' ");

        

        } else {
            $ret = $partner->Add();
        // $sapUp->query("UPDATE OCRD SET PymCode='{$this->paymentForm}'  WHERE CardCode='{$this->code}' ");

        }

        if ($ret !== 0) {
          $logsError = new logsError();
          $logsError->saveInDB('SAP00', 'Erro ao salvar no SAP',$sap->GetLastErrorDescription());
            throw new \Exception($sap->GetLastErrorDescription());
        } else {
            $this->code = $sap->GetNewObjectKey();
            $this->is_locked = false;
            $this->message = '';
            $this->save();
            
            /** @var Contract $contract */
            foreach ($this->contracts()->get() as $contract) {
                $contract->saveInSap($sap, $this);
            }

            $uploads = Upload::where('idReference', $this->id)->where('reference', 'partners')->first();
            if(!empty($uploads)){
                LinkUploadsInDocument::dispatch($uploads)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
            }
            // return true;
        }
        
    }
    public function saveInSapXML($oParners) {
            try {
                $sap = NewCompany::getInstance()->getCompany();
              /** @var IBusinessPartners $partner */
              $partner = $sap->GetBusinessObject(BoObjectTypes::oBusinessPartners);
              $update = false;
              if ($oParners->code) {
                  $partner->GetByKey((string)$oParners->code);
                  $update = true;
              } elseif ((int)$oParners->type === 1) {
                  $partner->Series = (int)Config::get('bps-series');
              } else {
                  $partner->Series = (int)Config::get('bpc-series');
              }
              $partner->CardCode = (string)$oParners->code;
              $partner->CardName = (string)$oParners->name;
              $partner->CardForeignName = (string)$oParners->fantasy_name;
              $partner->CardType = (int)$oParners->type;
              $partner->GroupCode = (int)$oParners->group;
              $partner->Phone1 = (string)$oParners->telephone;
              $partner->EmailAddress = (string)$oParners->email;
              $partner->FiscalTaxID->TaxId0 = (string)$oParners->cnpj;
              $partner->FiscalTaxID->TaxId1 = (string)$oParners->ie;
              $partner->FiscalTaxID->TaxId2 = (string)$oParners->ie_st;
              $partner->FiscalTaxID->TaxId3 = (string)$oParners->im;
              $partner->FiscalTaxID->TaxId4 = (string)$oParners->cpf;
              $partner->FreeText = (string)$oParners->comments;

              /** @var Address $address */
              foreach ($oParners->addresses()->orderByDesc('line')->get() as $address) {
                  $address->saveInSap($partner);
              }

              /** @var Contact $contact */
              foreach ($oParners->contacts()->orderByDesc('line')->get() as $contact) {
                  $contact->saveInSap($partner);
              }

              if ($update) {
                  $ret = $partner->Update();
              } else {
                  $ret = $partner->Add();
              }

              if ($ret !== 0) {
                $logsError = new logsError();
                $logsError->saveInDB('SAP01', 'Erro ao salvar no SAP',$sap->GetLastErrorDescription());
              }else{
                $oParners->code = $sap->GetNewObjectKey();
                $oParners->is_locked = false;
                $oParners->message = '';
                $oParners->save();
              }
            } catch (\Exception $e) {
              $oParners->is_locked = true;
              $oParners->message = $e->getMessage();
              $oParners->save();
              $logsError = new logsError();
              $logsError->saveInDB('SAP01', $e->getFile().'|'.$e->getLine(),$e->getMessage());
            }

    }

    public function updateUpload(){
        $attachment = Upload::where('reference', '=', 'partners')
            ->where('idReference', '=', $this->id)
            ->first();
        if(!is_null($attachment)){
          $sap = NewCompany::getInstance()->getCompany();
          $item = $sap->GetBusinessObject(BoObjectTypes::oBusinessPartners);
          $item->GetByKey((string)$this->code);
  
          $codeAttachment = $attachment->saveInSAP();
          if(!is_null($codeAttachment)){
            $item->AttachmentEntry = $codeAttachment;
          }
          
          $ret = $item->Update();
          
          if($ret !== 0){
            $this->message = $sap->GetLastErrorDescription();
            $this->save();
          }
        }
    }


    public static function cron()
    {
        /** @var Partner[] $items */
        $items = Partner::where("is_locked", "=", 1)->get();

        foreach ($items as $item) {
            try {
                $item->saveInSap();
            } catch (\Exception $e) {
                $item->message = $e->getMessage();
                $item->save();
            }
        }
    }

    public function getCpfCnpjAttribute()
    {
        return $this->cpf ? $this->cpf : $this->cnpj;
    }

    public static function partnerContracts($codPN){
        return Contract::where('cardCode', '=', $codPN)
                ->whereDate('startDate', '<=', date('Y-m-d'))
                ->whereDate('endDate', '>=', date('Y-m-d'))
                ->where('residualAmount', '>', 0)
                ->get();
        //return DB::SELECT("SELECT * FROM SAPHOMOLOGACAO.dbo.[@A2R_PNCONTRATOS] WHERE U_A2R_CODPN = :codPN AND U_A2R_DTINICIO <= '".date('Y-m-d')."' AND U_A2R_DTFIM >= '".date('Y-m-d')."' AND ", ['codPN' => $codPN]);
    }
}
