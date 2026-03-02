<!DOCTYPE HTML>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
          integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <style>
        @page {
            size: A4;
        }

        @media print {
            html, body {
                width: 210mm;
                height: 297mm;
            }
        }

        html, body {
            width: 210mm;
            height: 297mm;
        }

        .col-print-1 {
            width: 8%;
            float: left;
        }

        .col-print-2 {
            width: 16%;
            float: left;
        }

        .col-print-3 {
            width: 25%;
            float: left;
        }

        .col-print-4 {
            width: 33%;
            float: left;
        }

        .col-print-5 {
            width: 42%;
            float: left;
        }

        .col-print-6 {
            width: 50%;
            float: left;
        }

        .col-print-7 {
            width: 58%;
            float: left;
        }

        .col-print-8 {
            width: 66%;
            float: left;
        }

        .col-print-9 {
            width: 75%;
            float: left;
        }

        .col-print-10 {
            width: 83%;
            float: left;
        }

        .col-print-11 {
            width: 92%;
            float: left;
        }

        .col-print-12 {
            width: 100%;
            float: left;
        }

        table {
            width: 100%;
        }

        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        table#t01 tr:nth-child(even) {

        }

        table#t02 {
            width: 70%;
        }

        table#t02 th {
            background-color: black;
            color: white;
        }

        table#t01 tr:nth-child(odd) {
            background-color: #fff;
        }

        table#t01 th {
            background-color: black;
            color: white;
        }
    </style>
</head>
<body>
<?php
$path = public_path($img->diretory);
$type = pathinfo($path, PATHINFO_EXTENSION);
$data = file_get_contents($path);
$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data); ?>

<table style="width:50%; position:absolute; left: 350px;">
    <tr>
        <th style="width:50%;font-size:12px; background-color: black;color: white; height: auto; ">COMMERCIAL INVOICE/FATURA</th>
        <td style="width:50%; font-size:12px;">{{$head->code}}</td>
    </tr>
    <tr>
        <th style="width:50%; font-size:12px; background-color: black;color: white;">DATE</th>
        <td style="width:50%;font-size:12px;">{{formatDate($head->docDate)}} </td>
    </tr>
</table>
<img src="{{$base64}}" style="padding-left: 0; width: 120px;">
<br>
<br>
<table id="t01">
    <tr>
        <th id="th2" style="font-size:12px;">EXPORTER</th>
    </tr>
    <tr>
        <td colspan="2" style="font-size:12px;"> {{$company->company}}<br>
            {{$company->address}},
            LOTE:{{$company->number}} {{$company->neighborhood}} {{$company->city}} {{$company->cep}}
            <br> CNPJ:{{$company->cnpj}}</td>
    </tr>
</table>
<br>
<table id="t01">
    <tr>
        <th style="font-size:12px;">IMPORTER</th>
    </tr>
    <tr>
        <td colspan="2" style=" font-size:11px;">{{$head->cardName}}<br>
            {{isset($partner->Street) ? $partner->Street: '' }} {{isset($partner->StreetNo) ? $partner->StreetNo: '' }}  {{isset($partner->Block) ? $partner->Block: '' }}
            {{isset($partner->City) ? $partner->City: ''}} {{isset($partner->StreetNo) ? $partner->StreetNo: '' }}
        </td>
    </tr>
</table>
<br>
<br>
<table id="t01" style="padding-top: 5%;width: 100%!important;">
    <tr>
        <th style="font-size:11px;">REF. Number</th>
        <th style="font-size:11px;">NF. Number</th>
        <th style="font-size:11px;">Description</th>
        <th style="font-size:11px;">Quant. (KG)</th>
        <th style="font-size:11px;">Unit Price (EUR)</th>
        <th style="font-size:11px;">Total Price (EUR)</th>
    </tr>
    @foreach($head->items()->get() as $key => $value)
        <tr>
            <td style="font-size:11px;">{{$value->itemCode}}</td>
            <td style="font-size:11px;">{{$value->numberNF}}</td>
            <td style="font-size:11px;">{{$value->description}}</td>
            <td style="font-size:11px;">{{number_format($value->quantity,2,'.',',')}}</td>
            <td style="font-size:11px;">{{number_format($value->unitPrice,3,'.',',')}}</td>
            <td style="font-size:11px;">{{number_format($value->totalLine,2,'.',',')}}</td>
        </tr>
    @endForeach
</table>
<br>
<div class="row">
    <div class="col-md-5"></div>
    <div class="col-md-7 col-print-7" style="float: right">
        <table id="t03">
            <tr>
                <td> Freight</td>
                <td>{{number_format($head->freight,2,',','.')}}</td>
            </tr>
            <tr>
                <td> Insurance</td>
                <td>{{number_format($head->insurance,2,',','.')}}</td>
            </tr>
            <tr>
                <td> TOTAL</td>
                <td>{{number_format($head->docTotal,2,',','.')}}</td>
            </tr>
        </table>
    </div>
</div>
<br>
<div class="row" style="">
    <div class="col-md-5 col-print-5">
        <div>
            <table>
                <tr>
                    <td>CURRENCY:</td>
                    <td>{{$head->coin}}</td>
                </tr>
                <tr>
                    <td>INCOTERM:</td>
                    <td>{{$head->incoTerm}}</td>
                </tr>
                <tr>
                    <td>NCM:</td>
                    <td>{{$head->getNcmLabel()}}</td>
                </tr>
                <tr>
                    <td>GROSS WEIGHT:</td>
                    <td>{{number_format($head->grossWeigth,2,',','.')}}</td>
                </tr>
                <tr>
                    <td>NETWEIGHT(KG):</td>
                    <td>{{number_format($head->netWeigth,2,',','.')}}</td>
                </tr>
                <tr>
                    <td>VOLUME TYPE:</td>
                    <td>{{$head->volumeType}}</td>
                </tr>
            </table>
            <br>
            <table>
                <tr>
                    <td>ORIGIN:</td>
                    <td>{{$head->origin}}</td>
                </tr>
                <tr>
                    <td>COUNTRY OF ORIGIN:</td>
                    <td>{{$head->countryOrigin}}</td>
                </tr>
                <tr>
                    <td>DESTINY:</td>
                    <td>{{$head->destiny}}</td>
                </tr>
            </table>
        </div>
    </div>
    <div class="col-md-7 col-print-7" style="margin-top: 10mm">
        <div class="border border-dark p-2">
            <p>Account with: Standard Chartered Bank - Frankfurt</p>
            <p>Swift code: SCBLDEFX</p>
            <p>Account nr: 18250407</p>
            <p>In favor of: ITAU UNIBANCO SA</p>
            <p>Swift code: ITAUBRSPSPO</p>
            <p>For further credit to: SOLID ENERGIA RENOVAVEL LTDA</p>
            <p>Branch / Account number: 0334 / 15189-1</p>
            <p></p>
            <p>IBAN CODE: BR97 6070 1190 0033 4000 0151 891C 1</p>
        </div>
    </div>
</div>
</body>
</html>
