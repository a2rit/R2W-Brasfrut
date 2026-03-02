@extends('layouts.main')

@section('title', 'Impostos')

@section('content')

<div class="wrapper wrapper-content">
      <div class="row">
         <div class="col-lg-12">
            <div class="ibox ">
               <div class="ibox-title input-group-btn ">
                 <h5><a href="{{route('home')}}">Inicio</a> &nbsp;</h5> <h5>&nbsp;/&nbsp;</h5> <h5> &nbsp;<a href="{{route('settings.index')}}">Configurações </a>&nbsp;</h5><h5>&nbsp;/&nbsp;</h5> <h5> &nbsp;Impostos&nbsp;</h5>
               </div>
               <div class="ibox-content">
                  <div class="row">
                    <div class="col-md-12 form-group">
                      <div class="col-md-6">
                        <a href="{{route('settings.tax.create')}}"><img src="{{asset('images/importarXML.png')}}" style="width: 15%"><strong> Importador</strong></a>
                      </div>
                        <div class="col-md-6">
                          <a href="{{route('settings.tax.create.web.mania')}}"><img src="{{asset('images/web.png')}}" style="width: 15%"><strong> WEB Mania</strong></a>
                        </div>
                    </div>
                  </div>
               </div>
            </div>
        </div>
      </div>
</div>

@endsection
