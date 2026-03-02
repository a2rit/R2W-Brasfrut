<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Print Intern Consumption</title>
    <style>
        table {
            font-size: 8pt;
        }
        @media print {
            .no-print{
                display: none;
            }
        }
    </style>
</head>
<body style="text-align: center; width: 100%">
<div class="container" style="width: 80mm; text-align: center; display: inline-block">
    <div class="row" style="text-align: center">
        <h3>Yacht Clube da Bahia</h3>
        <h4>Consumo Interno {{mb_convert_case($ic->pos->nome, MB_CASE_TITLE)}}</h4>
        @if($ic->status === $ic::STATUS_CANCELED)
            <h4>CANCELADO!</h4>
        @endif
    </div>
    <div class="row">
        <table style="width: 100%">
            <tr>
                <th>Código</th>
                <th>Descrição</th>
                <th>Qtd</th>
                <th style="white-space: nowrap">Valor Un</th>
                <th>Subtotal</th>
            </tr>
            <?php /** @var \App\Modules\InternConsumption\Models\InternConsumption $ic */ ?>
            @foreach($ic->items as $item)
                <tr>
                    <td><b>{{$item->code}}</b></td>
                    <td><b>{{$item->name}}</b></td>
                    <td><b>{{$item->qty}}</b></td>
                    <td style="white-space: nowrap">R$ <b>{{number_format($item->value, 2, ',', '.')}}</b></td>
                    <td style="white-space: nowrap">R$ <b>{{number_format($item->total, 2, ',', '.')}}</b></td>
                </tr>
            @endforeach
        </table>
    </div>
    <br>
    <div class="row" style="text-align: left">
        <div class=""><strong>Total: </strong>R$ {{$ic->total}}</div>
        <div class=""><strong>Solicitante: </strong>{{$ic->requester_name}}</div>
        <div class=""><strong>Ramal: </strong>{{$ic->requester_branch}}</div>
        <div class=""><strong>Regra de Distribuição: </strong>{{$ic->distribution_rule_name}}</div>
        <div class=""><strong>Regra de Distribuição 2: </strong>{{$ic->distribution_rule2_name}}</div>
        <div class=""><strong>Projeto: </strong>{{$ic->project_name}}</div>
        <div class=""><strong>Data: </strong>{{$ic->date->format('d-m-Y')}}</div>
        <div class=""><strong>Local de entrega: </strong>{{$ic->delivery_location}}</div>
        <div class=""><strong>Comentário: </strong>{{$ic->comment}}</div>
        <div class=""><strong>Observações: </strong>{{$ic->observation}}</div>
    </div>
    <br>
    <br>
    @if($ic->status === $ic::STATUS_CANCELED)
        <h4>CANCELADO!</h4>
    @endif
    <br>
    <span>© {{date('Y')}} A2R Inovação em Tecnologia</span>
    <div>
        <button class="no-print" onclick="print();">Imprimir</button>
    </div>
</div>
<script>
    print();
</script>
</body>
</html>