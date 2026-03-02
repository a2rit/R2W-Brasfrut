<?php

namespace App\Models;

use App\ErrorTrait;
use App\Exceptions\SapIntegrationException;
use App\Exceptions\StockErrorException;
use App\Jobs\NFCe\CriarOP;
use App\Jobs\NFCe\LancarGorjetaSAP;
use App\Jobs\NFCe\SalvarPagamentoSAP;
use App\Jobs\NFCe\SalvarSAP;
use App\Jobs\Queue;
use App\Models\Colibri\NFc;
use App\Models\Colibri\Pagamento;
use App\Models\NFCe\Item;
use App\Models\NFCe\Pagamento as Pagamentos;
use DB;
use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Litiano\Sap\Enum\BoObjectTypes;
use Litiano\Sap\IdeHelper\IDocuments;
use Litiano\Sap\IdeHelper\IJournalEntries;
use Litiano\Sap\NewCompany;
use Log;
use SimpleXMLElement;
use Throwable;

/**
 * App\Models\NFCe
 *
 * @property int $id
 * @property string|null $id_colibri
 * @property int|null $pv_id
 * @property Carbon $data_emissao
 * @property int $numero
 * @property int $serie
 * @property string $versao
 * @property string $chave
 * @property string $natureza_operacao
 * @property float $subtotal
 * @property float $desconto
 * @property float|null $servicos
 * @property float $total
 * @property string $info_adicional
 * @property int|null $codigo_sap
 * @property int|null $conta_receber
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $lancamento_gorjeta
 * @property-read mixed $pagamentos_colibri
 * @property-read Collection|Item[] $itens
 * @property-read NFc $nfcColibri
 * @property-read PontoVenda|null $pv
 * @method static Builder|NFCe newModelQuery()
 * @method static Builder|NFCe newQuery()
 * @method static Builder|NFCe query()
 * @method static Builder|NFCe whereChave($value)
 * @method static Builder|NFCe whereCodigoSap($value)
 * @method static Builder|NFCe whereContaReceber($value)
 * @method static Builder|NFCe whereCreatedAt($value)
 * @method static Builder|NFCe whereDataEmissao($value)
 * @method static Builder|NFCe whereDesconto($value)
 * @method static Builder|NFCe whereErroId($value)
 * @method static Builder|NFCe whereId($value)
 * @method static Builder|NFCe whereIdColibri($value)
 * @method static Builder|NFCe whereInfoAdicional($value)
 * @method static Builder|NFCe whereLancamentoGorjeta($value)
 * @method static Builder|NFCe whereNaturezaOperacao($value)
 * @method static Builder|NFCe whereNumero($value)
 * @method static Builder|NFCe wherePvId($value)
 * @method static Builder|NFCe whereSerie($value)
 * @method static Builder|NFCe whereServicos($value)
 * @method static Builder|NFCe whereSubtotal($value)
 * @method static Builder|NFCe whereTotal($value)
 * @method static Builder|NFCe whereUpdatedAt($value)
 * @method static Builder|NFCe whereVersao($value)
 * @mixin Eloquent
 */
class NFCe extends Model
{
    use ErrorTrait;

    protected $table = 'nfc';

    protected $dates = ['created_at', 'updated_at', 'data_emissao'];

    /**
     * @param $nfe
     * @param PontoVenda|null $pv
     * @return mixed
     * @throws Throwable
     */
    public static function loadXml(SimpleXMLElement $nfe, PontoVenda $pv = null)
    {
        $success = DB::transaction(function () use ($nfe, $pv) {
            if (!$nfe || empty($nfe->ide->serie)) {
                throw new Exception('XML Inválido.');
            }

            if ((string)$nfe->emit->CNPJ != env('CNPJ_EMITENTE')) {
                //throw new \Exception('CNPJ não confere com emitente.');
            }

            $chave = preg_replace("/\D/", "", (string)$nfe['Id']);

            if (NFCe::nfceExiste($chave)) {
                // NFCe já está no banco.
                return false;
            }

            $serie = (int)$nfe->ide->serie;
            $pv = self::getPontoVenda($nfe);

            if (!$pv) {
                $pv = PontoVenda::where('serie', '=', $serie);
                if ($pv->count() > 1) {
                    throw new Exception("Existe mais de um PV com mesmo número de série.");
                }
                $pv = $pv->first();
                if (!$pv) {
                    throw new Exception("Não existe pv com número de série {$serie}.");
                }
            }

            $nfce = new NFCe();
            $nfce->pv_id = $pv->id;
            $nfce->numero = (int)$nfe->ide->nNF;
            $nfce->serie = $serie;
            $nfce->versao = (string)$nfe['versao'];
            $nfce->data_emissao = date($nfce->getDateFormat(), strtotime((string)$nfe->ide->dhEmi));
            $nfce->chave = $chave;
            $nfce->natureza_operacao = (string)$nfe->ide->natOp;
            $nfce->subtotal = (double)$nfe->total->ICMSTot->vProd; // Total sem desconto
            $nfce->desconto = (double)$nfe->total->ICMSTot->vDesc;
            $nfce->total = (double)$nfe->total->ICMSTot->vNF; // Total com desconto + frete + seguro
            $nfce->info_adicional = (string)$nfe->infAdic->infCpl;
            $nfce->save();
            /**
             * Se a NFC já existe no banco, aconte uma Exception e a função para por aqui.
             */

            /**
             * Salva os itens da NFC/
             */
            foreach ($nfe->det as $_item) {
                $prod = $_item->prod;
                $itemNfc = new Item();
                $itemNfc->codigo_pdv = (string)$prod->cProd;
                $itemNfc->nome = (string)$prod->xProd;
                $itemNfc->cfop = (int)$prod->CFOP;
                $itemNfc->ncm = (int)$prod->NCM;
                $itemNfc->unidade_comercial = (string)$prod->uCom;
                $itemNfc->quantidade = (double)$prod->qCom;
                $itemNfc->valor_unitario = (double)$prod->vUnCom;
                $itemNfc->desconto = (double)$prod->vDesc;
                $itemNfc->outros_valores = (double)$prod->vOutro;
                $itemNfc->total = (double)$prod->vProd + (double)$prod->vOutro - (double)$prod->vDesc;
                $itemNfc->nfc_id = $nfce->id;
                // Com children()[0] eu acesso o primeiro filho indepentende do nome, se é ICMS00, ICMS60 etc.
                // ICMS, PIS e COFINS só possuem um filho, portando, dá pra entrar com children()[0]
                // IPI tem mais filhos :(
                $itemNfc->cst_icms = (string)$_item->imposto->ICMS->children()[0]->orig . "." . (string)$_item->imposto->ICMS->children()[0]->CST;

                if (isset($_item->imposto->PIS)) {
                    $itemNfc->cst_pis = (string)$_item->imposto->PIS->children()[0]->CST;
                } else {
                    $itemNfc->cst_pis = "99"; // Outras operações. PIS não é obrigatorio no XML
                }

                if (isset($_item->imposto->COFINS)) {
                    $itemNfc->cst_cofins = (string)$_item->imposto->COFINS->children()[0]->CST;
                } else {
                    $itemNfc->cst_cofins = "99"; // Outras operações. COFINS não é obrigatorio no XML
                }

                if (isset($_item->imposto->IPI)) {
                    if (isset($_item->imposto->IPI->IPITrib)) {
                        $itemNfc->cst_ipi = (string)$_item->imposto->IPI->IPITrib->CST;
                    } elseif (isset($_item->imposto->IPI->IPINT)) {
                        $itemNfc->cst_ipi = (string)$_item->imposto->IPI->IPINT->CST;
                    } else {
                        $itemNfc->cst_ipi = "99"; //99=Outras saídas
                    }
                } else {
                    $itemNfc->cst_ipi = "99"; //99=Outras saídas
                }

                $itemNfc->save();

                $itemNfc->getItemSap();

            }

            /**
             * Salva os pagamentos da NFC/
             */
            foreach ($nfe->pag->detPag as $pag) {
                $pagamento = new Pagamentos();
                $pagamento->nfc_id = $nfce->id;
                $pagamento->tipo = (int)$pag->tPag;
                $pagamento->valor = (double)$pag->vPag;
                if ($pag->card) {
                    $pagamento->cnpj_credenciadora = $pag->card->CNPJ;
                    $pagamento->bandeira = $pag->card->tBand;
                    $pagamento->numero_autorizacao = $pag->card->cAut;
                }
                $pagamento->save();
            }

            return true;
        });

        if ($success) {
            $chave = preg_replace("/\D/", "", (string)$nfe['Id']);
            $nf = NFCe::where('chave', '=', $chave)->first();
            if ($nf) {
                $nf->checkAndSendToSap();
            }
        }

        return $success;
    }

    public function checkAndSendToSap()
    {
        $canSave = true;
        /** @var Item $item */
        foreach ($this->itens as $item) {
            /**
             * Se o Codigo SAP do Item não existe ou item é IP é ainda não foi dada entrada
             * o sistema ignora.
             */
            if (!$item->codigo_sap || ($item->tipo == "IP" && $item->codigo_op && !$item->codigo_entrada_item)) {
                $canSave = false;
                continue;
            }

            /** Verifica estoque antes de produzir */
            if ($item->paraProduzir()) {
                $canSave = false;
                CriarOP::dispatch($item->id)->onQueue(Queue::QUEUE_LOW);
            }
        }
        if ($canSave) {
            SalvarSAP::dispatch($this->id)->onQueue($this->erro()->exists() ? Queue::QUEUE_VERY_LOW : Queue::QUEUE_LOW);
        }
    }

    public static function tarefaSalvarSAP()
    {
        /** @var NFCe[] $nfs */
        $nfs = NFCe::whereNull('codigo_sap')->get();
        foreach ($nfs as $nf) {
            $nf->checkAndSendToSap();
        }
    }

    public static function tarefaAtualizarItensSemCodigoSap()
    {
        $itens = Item::whereNull('codigo_sap')->get();
        /** @var Item $item */
        foreach ($itens as $item) {
            if($item->getItemSap()) {
                $item->nfc->checkAndSendToSap();
            }
        }
    }

    public static function tarefaAtualizarItensComErro()
    {
        NFCe::tarefaAtualizarItensSemCodigoSap();

        /**
         * @iNFO Atualizar itens que podem está sem estrutura de produção
         * @var $NFCe NFCe
         */
        $NFCes = NFCe::whereNull("codigo_sap")->get();
        foreach ($NFCes as $NFCe) {
            foreach ($NFCe->itens as $item) {
                if ($item->tipo != "IP") {
                    $item->getItemSap();
                }
            }
        }
    }

    public static function tarefaSalvarPagamentosSap(bool $isFallback = false)
    {
        $query = NFCe::whereNull('conta_receber')
            ->whereNotNull('codigo_sap')
        ;

        if ($isFallback) {
            $query->where('data_emissao', '<=', Carbon::now()->subDay())
                ->doesntHave('recentError')
            ;
        }

        $nfs = $query->get(['id']);
        foreach ($nfs as $nf) {
            SalvarPagamentoSAP::dispatch($nf->id)->onQueue(Queue::QUEUE_LOW);
        }
    }

    public static function tarefaPagamentoServicos()
    {
        $pagamentos = Pagamento::where('gorjeta', '>=', '0.01')
            ->whereNull('gorjeta_codigo_sap')
            ->get()
        ;

        /** @var Pagamento $pagamento */
        foreach ($pagamentos as $pagamento) {
            try {
                DB::transaction(function () use ($pagamento) {
                    /** @var Pagamento $pag */
                    $pag = Pagamento::lockForUpdate()->find($pagamento->id);
                    $pag->salvarGorjetasSap();
                });
            } catch (Throwable $e) {
                $pagamento->createOrUpdateError($e);
                app('NFCeLogger')->error("Erro ao salvar pagamentosServicos no SAP {$pagamento->id}");
                app('NFCeLogger')->error($e);
            }
        }
    }

    public static function lancamentoGorjetaCron(bool $isFallback = false)
    {
        $pvs = PontoVenda::all();

        foreach ($pvs as $pv) {
            $query = NFCe::whereNotNull('codigo_sap')
                ->where('pv_id', $pv->id)
                ->whereNull('lancamento_gorjeta')
                ->whereDate('data_emissao', '>=', '2019-07-01')
                ->whereHas('itens', function (Builder $builder) use ($pv) {
                    $builder->where('codigo_sap', $pv->item_gorjeta_colibri);
                })
            ;

            if ($isFallback) {
                $query->where('data_emissao', '<=', Carbon::now()->subDay())
                    ->doesntHave('recentError')
                ;
            }
            $nfs = $query->get(['id']);

            /** @var NFCe $nf */
            foreach ($nfs as $nf) {
                LancarGorjetaSAP::dispatch($nf->id)->onQueue(Queue::QUEUE_LOW);
            }
        }
    }

    public static function nfceExiste($chave): bool
    {
        $chave = preg_replace("/[^0-9]/", "", (string)$chave);

        return NFCe::where("chave", "=", $chave)->exists();
    }

    protected static function getPontoVenda($nfe): ?PontoVenda
    {
        if (empty($nfe->infAdic->obsCont)) {
            return null;
        }

        $obsCont = $nfe->infAdic->obsCont;
        /** @var SimpleXMLElement $obs */
        foreach ($obsCont as $obs) {
            $attributes = $obs->attributes();

            if ((string)$attributes["xCampo"] === 'PDV') {
                return PontoVenda::find((string)$obs->xTexto);
            }
        }

        return null;
    }

    /**Nova função de salvar pagamento com dados direto da nota xml, anterior utilizava arquivos do colibri
     * @throws Exception
     */
    public function salvarPagamentosSAP()
    {
        if (!$this->canAddPayments()) {
            return true;
        }

        $sap = NewCompany::getInstance()->getCompany();
        $pv = $this->pv;
        $conta = $sap->GetBusinessObject(24); //oIncomingPayments
        $conta->CardCode = $pv->cliente;
        $conta->JournalRemarks = "Recebimento da NFCe {$this->numero} - Pagamento";
        $conta->DocDate = $this->data_emissao->format("d/m/Y");
        $conta->TaxDate = $this->data_emissao->format("d/m/Y");
        $conta->Invoices->DocEntry = (int)$this->codigo_sap;
        $conta->Invoices->InvoiceType = 13;

        $dinheiro = 0;
        $pix = 0;
        $transferRefs = [];

        /** @var Pagamentos $pagamento */
        foreach ($this->pagamentos as $pagamento) {
            /**
             * 01=dinheiro
             */
            if ((int)$pagamento->tipo === 1) {
                $dinheiro += $pagamento->valor;

                continue;
            }
            /**
             * 02=cheque
             */
            if ((int)$pagamento->tipo === 2) {
                $conta->Checks->CheckSum = (double)$pagamento->valor;
                $conta->Checks->CountryCode = "BR";
                $conta->Checks->BankCode = "3252";
                $conta->Checks->CheckNumber = (int)$this->numero;
                $conta->Checks->DueDate = $this->data_emissao->format("d/m/Y");
                $conta->Checks->Add();

                continue;
            }

            /**
             * 03=cartão de crédito
             * 04=cartão de débito
             * 10=vale alimentação
             * 11=vale refeição
             * 99=outros
             * OBS: Todos são tratados como crédito no SAP
             */
            if (in_array((int)$pagamento->tipo, [3, 4, 10, 11, 99], true)) {
                $conta->CreditCards->CardValidUntil = date('d/m/y');
                $conta->CreditCards->CreditCard = 2;
                $conta->CreditCards->CreditCardNumber = "1234567890";
                $conta->CreditCards->NumOfCreditPayments = 1;
                $conta->CreditCards->CreditSum = (double)$pagamento->valor;
                $conta->CreditCards->AdditionalPaymentSum = (double)$pagamento->valor;
                if ($pagamento->numero_autorizacao != "") {
                    $conta->CreditCards->VoucherNum = $pagamento->numero_autorizacao;
                } else {
                    $conta->CreditCards->VoucherNum = "012345";
                }
                $conta->CreditCards->PaymentMethodCode = 8;
                $conta->CreditCards->Add();

                continue;
            }

            /**
             * 05=crédito loja
             * 17=Pix
             * 19=Programa de fidelidade, Cashback, Crétido Virtual
             */
            if (in_array((int)$pagamento->tipo, [5, 17, 19], true)) {
                $pix += $pagamento->valor;
                $transferRefs[] = $pagamento->tipo;

                continue;
            }

            /**
             * Não prossegue se o tipo de pagamento for:
             * 12=vale presente
             * 13=vale combustivel
             * 15=boleto bancário
             * 16=depósito bancário
             * 18=Transferência bancária, carteira digital
             *
             * Retorna um erro e finaliza o processo.
             * OBS: Necessário criar tela de configuração para outros tipos de pagamento como:
             *          Permuta;Cortesia;Pre Pago;Outros
             */

            throw new Exception("Tipo de pagamento ({$pagamento->nome}) não mapeado. Cod NF SAP: {$this->codigo_sap}.");
        }

        if ($dinheiro > 0) {
            $conta->CashSum = (double)$dinheiro;
        }

        if ($pix > 0) {
            $conta->TransferAccount = $pv->conta_pix;
            $conta->TransferDate = $this->data_emissao->format("d/m/Y");
            $conta->TransferSum = (double)$pix;
            $conta->TransferReference = implode('-', $transferRefs);
            $conta->AccountPayments->Add();
        }

        if ($conta->Add() != "0") {
            throw new SapIntegrationException(
                "Erro ao adicionar pagamentos. Cod NF SAP: $this->codigo_sap. {$sap->GetLastErrorDescription()}",
                $sap->GetLastErrorCode()
            );
        }

        $this->destroyError();
        $this->conta_receber = $sap->GetNewObjectKey();
        $this->save();

        return true;
    }

    public function pv()
    {
        return $this->belongsTo(PontoVenda::class, 'pv_id', 'id');
    }

    public function getPagamentosColibriAttribute()
    {
        return $this->nfcColibri->pagamentos;
    }

    public function nfcColibri()
    {
        return $this->hasOne(NFc::class, "chave_nfce", "chave");
    }

    /**
     * @throws Exception
     */
    public function lancarGorjeta(): bool
    {
        if ($this->lancamento_gorjeta) {
            return false;
        }
        $pv = $this->pv;
        /** @var NewCompany $sap */
        $sap = NewCompany::getInstance();
        $company = $sap->getCompany();
        /** @var IJournalEntries $je */
        $je = $company->getBusinessObject(BoObjectTypes::oJournalEntries);
        $je->ReferenceDate = $this->data_emissao->format('d/m/Y');
        $je->DueDate = $this->data_emissao->format('d/m/Y');
        $je->TaxDate = $this->data_emissao->format('d/m/Y');
        $je->Memo = "Gorjeta NFCe {$this->numero} {$pv->nome} {$this->data_emissao->format('d-m-Y')}";
        $je->ProjectCode = $pv->projeto;

        $itensGorjeta = $this->itens()->where('codigo_sap', $pv->item_gorjeta_colibri)->get();
        /** @var Item $item */
        foreach ($itensGorjeta as $item) {
            $je->Lines->Credit = ($item->quantidade * $item->valor_unitario) - $item->desconto;
            $je->Lines->AccountCode = $pv->conta_gorjeta_credito;
            $je->Lines->ProjectCode = $pv->projeto;
            if ($pv->conta_gorjeta_credito !== '2.1.1.1.43') {
                $je->Lines->CostingCode = $pv->regra_distribuicao;
            }
            $je->Lines->Add();

            $je->Lines->Debit = ($item->quantidade * $item->valor_unitario) - $item->desconto;
            $je->Lines->AccountCode = $pv->conta_gorjeta_debito;
            $je->Lines->ProjectCode = $pv->projeto;
            $je->Lines->CostingCode = $pv->regra_distribuicao;
            $je->Lines->Add();
        }
        if ($je->Add() !== 0) {
            throw new SapIntegrationException(
                "Erro ao lançar gorjeta: {$company->GetLastErrorCode()}: {$company->GetLastErrorDescription()}",
                $company->GetLastErrorCode()
            );
        }

        $this->lancamento_gorjeta = $company->GetNewObjectKey();
        $this->save();
        $this->destroyError();

        return true;
    }

    public function itens(): HasMany
    {
        return $this->hasMany(Item::class, 'nfc_id', 'id');
    }

    public function inProductionItems(): HasMany
    {
        return $this->itens()
            ->whereNotNull('codigo_op')
            ->whereNotIn('status_op', [Item::STATUS_OP_FECHADO, Item::STATUS_OP_CANCELADO]);
    }

    public function pagamentos()
    {
        return $this->hasMany(Pagamentos::class, 'nfc_id', 'id');
    }

    /**
     * @throws Exception
     */
    public function salvarSAP(): bool
    {
        if (!$this->canSendToSap($this->chave)) {
            return true;
        }
        $pv = PontoVenda::find($this->pv_id);

        $fullTableName = \DB::connection()->getDatabaseName() . '.dbo.' . (new Item())->getTable();
        $hasStockError = \Litiano\Sap\NewCompany::getDb()
            ->table('OITW')
            ->join('OITM', 'OITM.ItemCode', 'OITW.ItemCode')
            ->join($fullTableName, "{$fullTableName}.codigo_sap", 'OITW.ItemCode')
            ->where('OITM.InvntItem', 'Y')
            ->where('OITW.WhsCode', $pv->deposito)
            ->where("{$fullTableName}.nfc_id", $this->id)
            ->groupBy(['OITW.ItemCode'])
            ->havingRaw("sum({$fullTableName}.quantidade) > max(OITW.OnHand)")
            ->exists()
        ;

        if ($hasStockError) {
            throw new StockErrorException("NFCe {$this->numero} com itens abaixo do estoque necessário.");
        }

        $sap = NewCompany::getInstance()->getCompany();
        /** @var IDocuments $nf */
        $nf = $sap->GetBusinessObject(13); // oInvoices

        $nf->Comments = mb_substr($this->info_adicional, 0, 200) . " Nota Fiscal do Consumidor {$this->numero}";
        $nf->CardCode = $pv->cliente;
        $nf->TaxDate = $this->data_emissao->format("d/m/Y");
        $nf->DocDate = $this->data_emissao->format("d/m/Y");
        $nf->DocDueDate = $this->data_emissao->format("d/m/Y");
        $nf->SalesPersonCode = $pv->vendedor; // Vendedor
        $nf->DocTotal = (double)$this->total;

        /** Campos de Usuário */
        $nf->UserFields->Fields->Item("U_chaveacesso")->Value = $this->chave;
        $nf->TaxExtension->NFRef = $this->chave; // Campo Referencia NF

        if ($this->desconto > 0) {
            // O desconto está no item.
            //$nf->DiscountPercent = (double)($this->desconto / $this->subtotal) * 100; // em porcentagem;
        }

        // Imposto
        $nf->SequenceCode = -2; // Int Cod Sequencia $nfe // -2 Externo
        $nf->SequenceModel = $pv->modelo_nf; // Sequencia do Modelo // Ver valores em tabela ONFM
        $nf->SeriesString = $this->serie; // Texto Nº de Série da $nf-e, no Sistema de Wilson é 2D
        $nf->SequenceSerial = (int)$this->numero; // Int Nº de Série da $nf-e

        //Contabilidade
        if ($pv->projeto) {
            $nf->Project = $pv->projeto;
        }

        foreach ($this->itens as $item) {
            if ($item->codigo_sap === $pv->item_gorjeta_colibri) {
                $item->codigo_sap = $pv->item_gorjeta_sap;
            }
            $nf->Lines->ItemCode = $item->codigo_sap;
            $nf->Lines->Quantity = (double)$item->quantidade;
            $nf->Lines->UnitPrice = (double)$item->valor_unitario;
            /**
             * @TODO
             * Adicionar a coluna "total" no tabela de itens.
             * Tirar o desconto e setar o LineTotal para verificar se o desconto aparece corretamente.
             */
            if ($item->total) {
                $nf->Lines->LineTotal = (double)$item->total;
            } elseif ($item->desconto > 0) {
                $nf->Lines->DiscountPercent = (double)(($item->desconto / $item->valor_unitario) * 100) / $item->quantidade;
                // em porcentagem
            }
            $nf->Lines->Usage = $pv->utilizacao; // Utilização - Buscar em OUSG
            $nf->Lines->CostingCode = $pv->regra_distribuicao; // Regra de distribuição
            $nf->Lines->CFOPCode = $item->cfop;
            $nf->Lines->TaxCode = $pv->codigo_imposto;
            $nf->Lines->WarehouseCode = $item->deposito;
            $nf->Lines->CSTforCOFINS = (string)$item->cst_cofins;
            $nf->Lines->CSTforPIS = (string)$item->cst_pis;
            $nf->Lines->CSTCode = (string)$item->cst_icms; // Origem.CST_ICMS
            $nf->Lines->CSTforIPI = (string)$item->cst_ipi;
            if ($pv->projeto) {
                $nf->Lines->ProjectCode = $pv->projeto;
            }
            if ($item->outros_valores > 0) {
                $nf->Lines->DistributeExpense = 1;//BoYesNoEnum.tYES; // 1
                $nf->Lines->Expenses->ExpenseCode = $pv->codigo_ov;
                $nf->Lines->Expenses->LineTotal = (double)$item->outros_valores;
                $nf->Lines->Expenses->DistributionRule = $pv->regra_distribuicao_ov;
                $nf->Lines->Expenses->TaxCode = $pv->codigo_imposto_ov;
                $nf->Lines->Expenses->Project = $pv->projeto_ov;
                $nf->Lines->Expenses->Add();
            }
            $nf->Lines->Add();
        }

        if ($nf->Add() != "0") {
            throw new SapIntegrationException(
                "Nº NF: $this->numero. {$sap->GetLastErrorDescription()}",
                $sap->GetLastErrorCode()
            );
        }

        $this->codigo_sap = $sap->GetNewObjectKey();

        $this->destroyError();
        $this->save();

        foreach ($this->itens as $item) {
            $item->destroyError();
        }

        return true;
    }

    protected function canSendToSap($chave): bool
    {
        $result = NewCompany::getDb()
            ->select("select top 1 DocEntry from OINV where OINV.U_chaveacesso = :chave", ["chave" => $chave]);
        if (count($result) == 0) {
            return true;
        }
        $this->codigo_sap = $result[0]->DocEntry;
        $this->save();
        $this->destroyError();
        return false;
    }

    protected function canAddPayments()
    {
        // Check if payment exists
        if ($this->codigo_sap) {
            $result = NewCompany::getDb()
                ->select("select top (1) RCT2.DocNum from RCT2 left join ORCT on ORCT.DocNum = RCT2.DocNum 
where ORCT.Canceled = 'N' and RCT2.InvType = 13 and RCT2.DocEntry = :docEntry", ["docEntry" => $this->codigo_sap]);

            if (count($result) == 0) {
                return true;
            }
            $this->conta_receber = $result[0]->DocNum;
            $this->destroyError();
            $this->save();
            return false;
        }
        return false;
    }
}
