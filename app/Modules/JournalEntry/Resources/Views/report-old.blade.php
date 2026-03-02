@extends('layouts.app')

@section('title', 'Main page')

@section('content')
    <div class="wrapper wrapper-content">
        <form action="{{route("journal-entry.reports/save")}}" method="post" onsubmit="return validate()" id="needs-validation">
            {!! csrf_field() !!}
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                          <h5><a href="{{route('home')}}">Inicio</a> &nbsp;</h5> <h5>&nbsp;/&nbsp;</h5> <h5><a href="{{route('journal-entry.index')}}"> &nbsp;Lançamento contábil manual</a>&nbsp;</h5> <h5>&nbsp;/&nbsp;</h5> <h5>Relatório</h5>
                        </div>
                        <div class="ibox-content">
                            <div class="row">
                              <div class="col-md-6">
                                     <div class="form-group"><label>Data Inicial</label>
                                         <div class="input-group">
                                             <input required data-mask="99/99/9999" type="text" name="start_date"
                                                    class="form-control datepicker" onchange="changePostingDate(this)" value="{{date('d/m/Y')}}">
                                             <div class="input-group-addon">
                                                 <span class="glyphicon glyphicon-th"></span>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                                 <div class="col-md-6">
                                       <div class="form-group"><label>Data Final</label>
                                           <div class="input-group">
                                               <input data-mask="99/99/9999" required type="text" name="finish_date"
                                                      class="form-control datepicker" value="{{date('d/m/Y')}}">
                                               <div class="input-group-addon">
                                                   <span class="glyphicon glyphicon-th"></span>
                                               </div>
                                           </div>
                                       </div>
                                   </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12  -align-right right pull-right right">
                    <div class="form-group pull-right">
                        <button class="btn btn-primary" type="submit">
                            Gerar
                        </button>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="hr-line-dashed"></div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $('.datepicker').datepicker({
            format: 'dd/mm/yyyy',
            autoclose: true,
            language: 'pt-BR',
            todayHighlight: true
        });
    </script>
@endsection
