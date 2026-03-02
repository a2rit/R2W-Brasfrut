<?php

namespace App\Modules\Partners\Models\Partner;

use Illuminate\Database\Eloquent\Model;

class BankAccounts extends Model
{
    protected $fillable = ['partner_id', 'BankCode', 'Branch', 'City', 'Street', 'Account','ControlKey', 'BankKey', 'BankName'];
    protected $table = 'partners_bankaccounts';


    public function saveInSap(&$partner)
    {
        $partner->BPBankAccounts->SetCurrentLine($this->line);
        
        $partner->BPBankAccounts->BPCode = $partner->CardCode;
        $partner->BPBankAccounts->AccountNo = $this->Account;
        $partner->BPBankAccounts->BankCode = $this->BankCode;
        $partner->BPBankAccounts->Branch = $this->Branch;
        isset($this->ControlKey) ? $partner->BPBankAccounts->ControlKey = $this->ControlKey : null;
        isset($this->City) ? $partner->BPBankAccounts->City = $this->City : null;
        isset($this->Street) ? $partner->BPBankAccounts->Street = $this->Street : null;
        $partner->BPBankAccounts->Add();
    }
}
