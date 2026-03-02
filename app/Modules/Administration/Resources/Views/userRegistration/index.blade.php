@extends('layouts.app')

@section('title', 'Usuários')

@section('content')

<div class="wrapper wrapper-content">
   <form action="" method="post" id="needs-validation" enctype="multipart/form-data">
      {!! csrf_field() !!}
      <div class="row">
         <div class="col-lg-12">
            <div class="ibox ">
               <div class="ibox-title input-group-btn ">
                 <h5><a href="{{route('home')}}">Inicio</a> &nbsp;</h5> <h5>&nbsp;/&nbsp;</h5> <h5> &nbsp;Usuários&nbsp;</h5>
               </div>
               <div class="ibox-content">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="col-md-6">
                        <a href="{{route('administration.user.registration.create')}}"><img src="{{asset('images/newDocument.png')}}" style="width: 15%"><strong> Cadastrar</strong></a>
                      </div>
                        <div class="col-md-6">
                          <a href="{{route('administration.user.registration.search')}}"><img src="{{asset('images/searchDocument.png')}}" style="width: 15%"><strong> Consultar</strong></a>
                        </div>
                    </div>
                  </div>
                     <div class="row" style="padding-top: 5%">
                          <div class="col-md-12">
                            <div class="col-md-6">
                              <a href="{{route('administration.user.registration.relatory')}}"><img src="{{asset('images/report.png')}}" style="width: 15%"><strong> Relatórios</strong></a>
                            </div>
                              <div class="col-md-6">
                                <a href="{{route('administration.user.registration.approver')}}"><img src="{{asset('images/admin.png')}}" style="width: 15%"><strong> Aprovadores</strong></a>
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
