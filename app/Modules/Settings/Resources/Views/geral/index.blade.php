@extends('layouts.main')
@section('title', 'Configurações Gerais')
@section('content')
<div class="wrapper wrapper-content">
   <form action="<?php echo route('settings.geral.save'); ?>" method="post" id="needs-validation" enctype="multipart/form-data"  onsubmit="waitingDialog.show('Salvando...')">
      {!! csrf_field() !!}
      <div class="row" id='form'>
         <div class="col-lg-12">
            <div class="ibox">
               <div class="ibox-title input-group-btn">
                  <h5><a href="{{route('home')}}">Inicio</a> &nbsp;</h5>
                  <h5>&nbsp;/&nbsp;</h5>
                  <h5><a href="{{route('settings.index')}}"> &nbsp;Configurações Gerais</a>&nbsp;</h5>
                  <h5>&nbsp;/&nbsp;</h5>
                  <h5>Cadastrar</h5>
               </div>
               <div class="ibox-content">
                  <div class="row">
                     <div class="col-md-6">
                        <label>Empresa:</label>
                        <div class="form-group">
                          @if(isset($settings[0]->id))
                           <input type="hidden" class="form-control" value="{{$settings[0]->id}}" name="id">
                           @endif
                           <input type="text" class="form-control"   @if(isset($settings[0]->id))  value="{{$settings[0]->company}}" @endif name="company">
                        </div>
                     </div>
                     <div class="col-md-6">
                        <label>CNPJ:</label>
                        <div class="form-group">
                           <input type="text" class="form-control"  @if(isset($settings[0]->id)) value="{{$settings[0]->cnpj}}" @endif name="cnpj">
                        </div>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-5">
                        <label>Endereço:</label>
                        <div class="form-group">
                           <input type="text" class="form-control"  @if(isset($settings[0]->id))   value="{{$settings[0]->address}}" @endif name="address">
                        </div>
                     </div>
                     <div class="col-md-1">
                        <label>Numero:</label>
                        <div class="form-group">
                           <input type="text" class="form-control"  @if(isset($settings[0]->id))   value="{{$settings[0]->number}}" @endif name="number">
                        </div>
                     </div>
                     <div class="col-md-2">
                        <label>Bairro:</label>
                        <div class="form-group">
                           <input type="text" class="form-control"  @if(isset($settings[0]->id))   value="{{$settings[0]->neighborhood}}" @endif name="neighborhood">
                        </div>
                     </div>
                     <div class="col-md-2">
                        <label>CEP:</label>
                        <div class="form-group">
                           <input type="text" class="form-control"  @if(isset($settings[0]->id))   value="{{$settings[0]->cep}}" @endif name="cep">
                        </div>
                     </div>
                     <div class="col-md-2">
                        <label>Cidade:</label>
                        <div class="form-group">
                           <input type="text" class="form-control"  @if(isset($settings[0]->id))   value="{{$settings[0]->city}}" @endif name="city">
                        </div>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-4">
                        <label>Telefone:</label>
                        <div class="form-group">
                           <input type="text" class="form-control"  @if(isset($settings[0]->id))   value="{{$settings[0]->telephone}}" @endif name="telephone">
                        </div>
                     </div>
                     <div class="col-md-4">
                        <label>Telefone 2:</label>
                        <div class="form-group">
                           <input type="text" class="form-control"  @if(isset($settings[0]->id))   value="{{$settings[0]->telephone2}}" @endif name="telephone2">
                        </div>
                     </div>
                     <div class="col-md-4">
                        <label>E-mail:</label>
                        <div class="form-group">
                           <input type="email" class="form-control"  @if(isset($settings[0]->id))   value="{{$settings[0]->email}}" @endif name="email">
                        </div>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-6">
                       <label>Logo</label>
                        <!-- image-preview-filename input [CUT FROM HERE]-->
                        <div class="input-group image-preview">
                           <input type="text" class="form-control image-preview-filename" disabled="disabled"> <!-- don't give a name === doesn't send on POST/GET -->
                           <span class="input-group-btn">
                              <!-- image-preview-clear button -->
                              <button type="button" class="btn btn-default image-preview-clear" style="display:none;">
                              <span class="glyphicon glyphicon-remove"></span> Remover
                              </button>
                              <!-- image-preview-input -->
                              <div class="btn btn-default image-preview-input">
                                 <span class="glyphicon glyphicon-folder-open"></span>
                                 <span class="image-preview-input-title">Abrir</span>
                                 <input type="file" multiple name="input-file-preview[]"/> <!-- rename it -->
                              </div>
                           </span>
                        </div>
                        <!-- /input-group image-preview [TO HERE]-->
                     </div>
                       <div class="col-md-6">
                          @if(isset($img[0]->diretory))
                           <center><img src="{{$img[0]->diretory}}" class="img-reponsive" style=" max-width:400px;max-height:150px;width: auto;height: auto;"></center>
                          @endif
                      </div>
               </div>
              </div>
            </div>
         </div>
      </div>
      <div class="col-md-12  -align-right right pull-right right">
         <div class="form-group pull-right">
            <button class="btn btn-primary" type="submit" >Salvar</button>
            <div class="hr-line-dashed"></div>
            <div class="hr-line-dashed"></div>
            
         </div>

      </div>
</form>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
$(document).on('click', '#close-preview', function(){
    $('.image-preview').popover('hide');
    // Hover befor close the preview
    $('.image-preview').hover(
  function () {
     $('.image-preview').popover('show');
  },
   function () {
     $('.image-preview').popover('hide');
  }
);
 });
$(function() {
  // Create the close button
  var closebtn = $('<button/>', {
      type:"button",
      text: 'x',
      id: 'close-preview',
      style: 'font-size: initial;',
  });
  closebtn.attr("class","close pull-right");
  // Set the popover default content
  $('.image-preview').popover({
      trigger:'manual',
      html:true,
      title: "<strong>Preview</strong>"+$(closebtn)[0].outerHTML,
      content: "There's no image",
      placement:'bottom'
  });
  // Clear event
  $('.image-preview-clear').click(function(){
      $('.image-preview').attr("data-content","").popover('hide');
      $('.image-preview-filename').val("");
      $('.image-preview-clear').hide();
      $('.image-preview-input input:file').val("");
      $(".image-preview-input-title").text("Browse");
  });
  // Create the preview image
  $(".image-preview-input input:file").change(function (){
      var img = $('<img/>', {
          id: 'dynamic',
          width:250,
          height:200
      });
      var file = this.files[0];
      var reader = new FileReader();
      // Set preview image into the popover data-content
      reader.onload = function (e) {
          $(".image-preview-input-title").text("Change");
          $(".image-preview-clear").show();
          $(".image-preview-filename").val(file.name);
          img.attr('src', e.target.result);
          $(".image-preview").attr("data-content",$(img)[0].outerHTML).popover("show");
      }
      reader.readAsDataURL(file);
  });
});
</script>
@endsection
