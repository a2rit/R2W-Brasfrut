@extends('auth.app')
@section('title') Login @endsection
@section('content')
  <div class="container-fluid mt-5" style="width: 25vw;">
    <div class="card">
      <div class="card-header">
        <figure>
          <a href="/">
            <img class="img-fluid center-block d-block mx-auto mt-3" style="width: 120px;" src="{{ url('img/logo-nova.png') }}" alt="4U Construções" />
          </a>
        </figure>
      </div>
      <div class="card-body">
        <form role="form" method="POST" action="{{ url('/login') }}">
          <fieldset>
            {{ csrf_field() }}
            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
              <label for="">E-Mail:</label>
              <input class="form-control" placeholder="E-mail" name="email" value="{{ old('email') }}"
                type="email" autofocus>
              @if ($errors->has('email'))
                <span class="help-block">
                  <strong>{{ $errors->first('email') }}</strong>
                </span>
              @endif
            </div>
            <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }} mt-2">
              <label for="">Senha:</label>
              <input class="form-control" placeholder="Senha" name="password" type="password" value="">
              @if ($errors->has('password'))
                <span class="help-block">
                  <strong>{{ $errors->first('password') }}</strong>
                </span>
              @endif
            </div>
            <div class="row">
              <div class="checkbox mt-3">
                <input name="remember" type="checkbox" class="form-check-input ms-1" style="width: 20px; height: 20px;">
                <label class="ms-1">Lembre-me</label>
              </div>
              <!-- Change this to a button or input when using this as a form -->
              <div class="col-12 text-center mt-3">
                <button type="submit" class="btn btn-primary">Entrar</button>
              </div>
              <a class="btn btn-link" href="{{ url('/password/reset') }}">
                Esqueceu a senha?
              </a>
            </div>
          </fieldset>
        </form>
      </div>
    </div>
  </div>
@endsection
