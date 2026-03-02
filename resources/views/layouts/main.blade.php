<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>R2W - @yield('title')</title>
  <link rel="icon" type="image/x-icon" href="{{ asset('/images/favicon.ico') }}">
  <link href="{{ mix('css/app.css') }}" rel="stylesheet" type="text/css">
  <script src="{{mix("js/app.js")}}" type="application/javascript"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

</head>

<body>
  @include('layouts.navigation')
  <div class="wrapper d-flex flex-column min-vh-100 bg-light">
    <div id="container-loader">
      <div id="loader">Loading...</div>
    </div>
    @include('layouts.topNav')
    <div class="body flex-grow-1">
      @include('layouts.alerts')
      <div class="container-fluid">
        @yield('content')
      </div>
    </div>
    @include('layouts.footer')

  </div>
</body>
<!-- /#wrapper -->

<script src="{{mix("js/bottom-app.js")}}" type="application/javascript"></script>
<script src="{!! asset('js/format.js') !!}" type="text/javascript"></script>

<script>
  //registra todos os erros do frontend na tabela logs_errors
  window.addEventListener("error", (ErrorEvent) => {
    $.ajax({
      type: 'POST',
      url: "{{ route('registerFrontendError') }}",
      data: {
        'message': ErrorEvent.message,
        'filename': ErrorEvent.filename
      },
      headers: {
        'X-CSRF-TOKEN': $('body input[name="_token"]').val()
      }
    });
  });

  $(document).on('changed.bs.select', '.selectpicker', function(event) {
    if (!$(event.target).attr('class').includes('ajax')) {
      $(event.target).selectpicker('toggle');
    }
  });

  $.each($('input,textarea,select').not('input[type="hidden"]').filter('[required]'), function(index, value) {
    $(value).parent().find('label').append(
      `<span class="text-orange ms-2 fw-bold text-tooltip" data-coreui-toggle="tooltip" title="Campo obrigatório">*</span>`
      );
  });

  $(document).ready(() => {
    $('.text-tooltip').tooltip();
  });

  $(document).on('blur', '.qtd', function(event) {
    $(event.target).val(
      parseFloat(
        $(event.target).val().replace(/[/^[^*|\":<>[\]{}`\\()';@&$]]/gi, '').replace(/[,]/gi, '.')
      ).format(3, ",", ".")
    );
  });

  $(document).on('blur', '.moneyPlus', function(event) {
    $(event.target).val(
      parseFloat(
        $(event.target).val().replace(/[/^[^*|\":<>[\]{}`\\()';@&$]]/gi, '').replace(/[.]/gi, '').replace(/[,]/gi, '.')
      ).format(4, ",", ".")
    );
  });


  $("body").on("keydown", "input", function(e) {
    var keyCode = e.keyCode || e.which;
    var form = $("form");
    if (keyCode == 9 && $("input:focus").length) {
      var focusable = $(form).find("input").filter(":visible");
      var next = focusable.eq(focusable.index(this) + 1);
      $(next).focusin(function() {
        $(this).maskMoney("destroy");
        $(this).select();
      });
    }
  });

  let currentScrollPosition = 0;

  $('.modal').on('show.coreui.modal', event => {
    currentScrollPosition = $(window).scrollTop();
  });

  $('.modal').on('hide.coreui.modal', event => {
    var $target = $('html,body');
    $target.animate({
      scrollTop: currentScrollPosition
    }, 0);
  });

  $('.dtrangepicker').daterangepicker(daterangepickerConfig);

  $('input[type="date"]').prop('max', '9999-12-31');

  Chart.defaults.global.legend.labels.usePointStyle = true;
  let selectpickerConfig = {
    "selectOnTab": true,
    "liveSearch": true,
    "style": "btn-default",
    "size": 5
  }

  $(function() {
    // Create the close button
    var closebtn = $('<button/>', {
      type: "button",
      text: 'x',
      id: 'close-preview',
      style: 'font-size: initial;',
    });
    closebtn.attr("class", "close pull-right");
    // Set the popover default content
    $('.image-preview').popover({
      trigger: 'manual',
      html: true,
      title: "<strong>Preview</strong>" + $(closebtn)[0].outerHTML,
      content: "There's no image",
      placement: 'bottom'
    });
    // Clear event
    $('.image-preview-clear').click(function() {
      $('.image-preview').attr("data-content", "").popover('hide');
      $('.image-preview-filename').val("");
      $('.image-preview-clear').hide();
      $('.image-preview-input input:file').val("");
      $(".image-preview-input-title").text("Browse");
    });
    // Create the preview image
    $(".image-preview-input input:file").change(function() {
      var img = $('<img/>', {
        id: 'dynamic',
        width: 250,
        height: 200
      });
      var file = this.files[0];
      var reader = new FileReader();
      // Set preview image into the popover data-content
      reader.onload = function(e) {
        $(".image-preview-input-title").text("Change");
        $(".image-preview-clear").show();
        $(".image-preview-filename").val(file.name);
        img.attr('src', e.target.result);
        $(".image-preview").attr("data-content", $(img)[0].outerHTML).popover("show");
      };
      reader.readAsDataURL(file);
    });
  });

  $(document).on('click', '#close-preview', function() {
    $('.image-preview').popover('hide');
    // Hover befor close the preview
    $('.image-preview').hover(
      function() {
        $('.image-preview').popover('show');
      },
      function() {
        $('.image-preview').popover('hide');
      }
    );
  });

  function getNotifications() {
    //Get notifications
    if (!("Notification" in window)) {
      alert("Este navegador não suporta notificações! Atualize!");
      return;
    } else if (Notification.permission === "granted") {
      axios.get("{{ route('getNotifications') }}")
        .then(function(response) {
          response.data.forEach(function(item) {
            let notification = new Notification(item.data.title, {
              body: item.data.message,
              icon: "{{ asset('img/logo-nova.png') }}",
            }).onclick = () => {
              if (item.data.url) {
                window.location.href = item.data.url;
              }
            };
            let sound = new Audio("{{ asset('sound/Windows Exclamation.wav') }}");
            sound.play();
          });
        });
    } else if (Notification.permission !== 'denied') {
      Notification.requestPermission();
    }

    setTimeout(getNotifications, 1000 * 30);
  }

  getNotifications();
</script>
@yield('scripts')
</body>

</html>
