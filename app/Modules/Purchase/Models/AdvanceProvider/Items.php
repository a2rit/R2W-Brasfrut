<?php

namespace App\Modules\Purchase\Models\AdvanceProvider;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Purchase\Models\AdvanceProvider\AdvanceProvider;
use App\logsError;

class Items extends Model
{
    protected $table = 'advance_provider_items';



    /**
     * Get the user that owns the Item
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function items(): BelongsTo
    {
        return $this->belongsTo(AdvanceProvider::class, 'idAdvanceProvider', 'id');
    }

    public function getDocTotal() {
        return $this->items()->sum(DB::raw('quantity * price'));
    }

    public function saveInDB($idAdvanceProvider, $value){
        $this->idAdvanceProvider = $idAdvanceProvider;
        $this->itemCode = $value['itemCode'];
        $this->itemName = $value['itemName'];
        $this->itemUnd = $value['itemUnd'];
        $this->quantity = is_numeric($value['qtd']) ? $value['qtd'] : clearNumberDouble($value['qtd']);
        $this->price = is_numeric($value['preco']) ? $value['preco'] : clearNumberDouble($value['preco']);
        $this->project = $value['projeto'];
        $this->distrRule = $value['costCenter'];
        $this->distrRule2 = $value['costCenter2'] ?? '';
        $this->save();
    }
}
