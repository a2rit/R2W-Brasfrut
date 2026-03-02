<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório</title>
    <style>
        td {
            font-size: 11pt;
        }
        .table-bordered, .table-bordered td, .table-bordered th {
            border: 1px solid black;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .table-bordered {
            border-collapse: collapse;
        }
        .text-center {
            text-align: center;
        }

        table {
            width: 100%;
        }

        .items-table {
            text-align: center;
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
    </style>
</head>
<body>
<div>
    <table>
        <tr>
            <td><img src="{{asset('img/logo-nova.png')}}" alt="" width="150px"></td>
            <td>
                <h3>YACHT CLUBE DA BAHIA</h3>
                <span>Av. Sete de Setembro - 3252</span><br>
                <span>Barra</span><br>
                <span>Salvador - BA-CEP:40130-001</span><br>
                <span>(71) 2101-9111</span><br>
            </td>
            <td><h3 style="text-align: center">Período: {{$h3}}</h3>
                <h3 style="text-align: center">Data: {{now()->format('d/m/Y')}}</h3></td>
        </tr>
        <tr>
            <td colspan="3">
                <hr>
            </td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: center"><h3>{{$title}}</h3></td>
        </tr>
    </table>
    @yield('content')
</div>
</body>
</html>