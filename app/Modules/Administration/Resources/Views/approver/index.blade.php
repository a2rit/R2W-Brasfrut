@extends('layouts.main')

@section('title', 'Aprovadores')

@section('content')

<div class="wrapper wrapper-content">
   <form action="" method="post" id="needs-validation" enctype="multipart/form-data">
      {!! csrf_field() !!}
      <div class="row">
         <div class="col-lg-12">
            <div class="ibox ">
               <ol class="breadcrumb">
                  <li>
                      <i class="fa fa-dashboard"></i> <a href="/">Home</a>
                  </li>
                  <li class="active">
                     <i class="fa fa-edit"></i>Aprovadores
                  </li>
              </ol>
               <div class="ibox-content">
                  <div class="row">
                    <div class="col-md-12">
                        <div class="col-md-6">
                           <a href="{{route('settings.lofted.index')}}"><img src="{{asset('images/admin_list.png')}}" style="width: 15%"><strong>Alçadas</strong></a>
                        </div>
                        <div class="col-md-6">
                           <a href="{{route('administration.user.registration.approver')}}"><img src="{{asset('images/admin.png')}}" style="width: 15%"><strong> Aprovadores </strong></a>
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
