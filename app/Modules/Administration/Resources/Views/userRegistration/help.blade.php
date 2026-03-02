@extends('layouts.app')

@section('title', 'Cadastro de Usuário')

@section('content')

<div class="wrapper wrapper-content">
   <form action="" method="post" id="needs-validation" enctype="multipart/form-data">
      {!! csrf_field() !!}
      <div class="row">
         <div class="col-lg-12">
            <div class="ibox ">
               <div class="ibox-title input-group-btn ">
                 <h5><a href="{{route('home')}}">Inicio</a> &nbsp;</h5> <h5>&nbsp;/&nbsp;</h5> <h5> &nbsp;Ajuda&nbsp;</h5>
               </div>
               <div class="ibox-content">
                  <div class="row">

                    <div class="col-lg-12">
                        <div class="jumbotron">
                            <h2><center>Entrar em contato com o Suporte</center></h2>

                            <p><center> suporte@a2r-it.com.br  <span class="btn btn-primary btn-xs"><i class="fa fa-at"></i></span></center></p>

                            <p><center>+55 71 3565 6598   <span class="btn btn-primary btn-xs"><i class="fa fa-phone-square"></i></span></center></p>
                            <p><center> www.a2r-it.com.br </center></p>
                        </div>
                    </div>

                  </div>

               </div>
            </div>
        </div>
      </div>
   </form>
</div>

@endsection
