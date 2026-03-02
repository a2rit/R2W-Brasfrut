<!DOCTYPE html>
<html lang="pt">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Styles -->
  <title>R2W - @yield('title')</title>
  <link rel="icon" type="image/x-icon" href="{{ asset('/images/favicon.ico') }}">
  <link href="{{ mix('css/app.css') }}" rel="stylesheet" type="text/css">
  @yield('css')
  <script src="{{mix("js/app.js")}}" type="application/javascript"></script>

  <!-- Scripts -->
  <script>
    window.Laravel = <?php echo json_encode([
        'csrfToken' => csrf_token(),
    ]); ?>
  </script>
</head>

<body>
  <div id="app">
    <div class="wrapper d-flex flex-column min-vh-100 bg-light">
      <header class="header header-sticky mb-4">
        <div class="container-fluid">
          <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ asset('img/ready2work.png') }}" alt="Ready to Work" height="28px;">
          </a>
        </div>
      </header>
      <div class="body flex-grow-1 px-3 ">
          <div class="container-fluid">
              @yield('content')
          </div>
        </div>
        @include("layouts.footer")
    </div>
  <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
    {{ csrf_field() }}
  </form>
  <script src="{{ url('js/app.js') }}"></script>
</body>

</html>
