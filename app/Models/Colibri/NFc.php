<?php

namespace App\Models\Colibri;

use Carbon\Carbon;
use DB;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use SimpleXMLElement;
use Throwable;

/**
 * App\Models\Colibri\NFc
 *
 * @property string $colibri_id
 * @property string $chave_nfce
 * @property Carbon $emissao
 * @property float $valor
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Pagamento[] $pagamentos
 * @method static Builder|NFc whereChaveNfce($value)
 * @method static Builder|NFc whereColibriId($value)
 * @method static Builder|NFc whereCreatedAt($value)
 * @method static Builder|NFc whereEmissao($value)
 * @method static Builder|NFc whereUpdatedAt($value)
 * @method static Builder|NFc whereValor($value)
 * @mixin Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Colibri\NFc newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Colibri\NFc newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Colibri\NFc query()
 */
class NFc extends Model
{
    public $incrementing = false;
    protected $table = "colibri_nfc";
    protected $primaryKey = "colibri_id";
    protected $dates = ["created_ad", "updated_at", "emissao"];

    /**
     * @param $xml
     * @return bool
     * @throws Throwable
     */
    public function loadXml(SimpleXMLElement $xml)
    {
        DB::transaction(function () use ($xml) {
            foreach ($xml->{"fiscal.comprovante"}->{"fiscal.comprovante"} as $item) {
                if ((string)$item["nfe_chave"] == "") {
                    continue;
                }
                $nfcColibri = NFc::where('colibri_id', '=', (string)$item["comprovante_id"])->first();
                if (!$nfcColibri) {
                    $nfcColibri = new NFc();
                }
                $nfcColibri->colibri_id = (string)$item["comprovante_id"];
                $nfcColibri->valor = str_replace(",", ".", (string)$item["valor"]);
                $nfcColibri->emissao = date($this->getDateFormat(), strtotime((string)$item["dt_emissao"]));
                $nfcColibri->chave_nfce = preg_replace("/[^0-9]/", "", (string)$item["nfe_chave"]);
                $nfcColibri->save();
            }
        });
        return true;
    }

    public function pagamentos()
    {
        return $this->hasMany(Pagamento::class, "colibri_nfc_id", "colibri_id")
            ->orderByDesc('valor');
    }
}
