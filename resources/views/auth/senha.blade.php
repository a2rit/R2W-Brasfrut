@extends('layouts.main')

@section('content')
  <div class="container-fluid w-50">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title text-center mt-2">Alterar Senha</h5>
      </div>
      <div class="card-body">
        <form class="form d-flex-col justify-content-center align-items-center" role="form" method="POST"
          action="{{ route('mudarSenhaPost') }}">
          {{ csrf_field() }}

          <div class="row mb-3{{ $errors->has('password') ? ' has-error' : '' }}">
            <label for="password" class="col-sm-3 col-form-label">Senha:</label>

            <div class="col-sm-9">
              <input id="password" type="password" class="form-control" name="password" required>

              @if ($errors->has('password'))
                <span class="help-block">
                  <strong>{{ $errors->first('password') }}</strong>
                </span>
              @endif
            </div>
          </div>

          <div class="row mb-3">
            <label for="new-password" class="col-sm-3 control-label">Nova Senha</label>

            <div class="col-sm-9">
              <input id="new-password" type="password" class="form-control" name="new_password" required>
            </div>
          </div>

          <div class="row mb-3">
            <label for="new-password-confirm" class="col-sm-3 control-label">Confirme</label>

            <div class="col-sm-9">
              <input id="new-password-confirm" type="password" class="form-control" name="password_confirmation" required>
            </div>
          </div>
          <div class="row text-center">
            <button type="submit" class="btn btn-primary d-grid gap-2 col-2 mx-auto">Salvar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  @endsection

  @section('scripts')
    <script></script>
  @endsection
