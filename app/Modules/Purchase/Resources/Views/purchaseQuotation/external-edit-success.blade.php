<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>R2W - Cotação Externa</title>
  <link rel="icon" type="image/x-icon" href="{{ asset('/images/favicon.ico') }}">
  <link href="{{ mix('css/app.css') }}" rel="stylesheet" type="text/css">
  <script src="{{mix("js/app.js")}}" type="application/javascript"></script>
</head>

<body>
  <div class="wrapper d-flex flex-column min-vh-100 bg-light">
    <div class="body flex-grow-1 px-3 pt-4">
      <div class="container-fluid">
        <div class="card">
          <div class="card-body text-center">
            <h1>Tudo certo!</h1>
            <h5>A cotação foi salva com sucesso. Em breve nosso time de compras enviará um retorno.</h5>
            <h5>Caso tenha necessidade de alterar a cotação, por favor, nos contate através do e-mail.</h5>
            <h5>Desde já a A2R-IT agradece a parceria!</h5>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
