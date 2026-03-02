@extends('partners::create')

@section('scripts')
    @parent
    <script type="application/javascript">
        @foreach($partner->getAttributes() as $key => $value)

            @if($key === 'ie' && $value === 'Isento')
                $('#iie').prop('checked', true);
                changeIie();
                @continue
            @endif

            if ($("[name='{{$key}}']").length > 0) {
                $("[name='{{$key}}']").val('{{$value}}');
            }

        @endforeach
        $.each(@json($partner->addresses), function (index, data) {
            var form = $('#addressForm');
            form.find('input,select').each(function (index, item) {
                item.value = data[item.name];
            });
            addAddress();
        });

        $.each(@json($partner->contacts), function (index, data) {
            var form = $('#contactForm');
            form.find('input,select').each(function (index, item) {
                item.value = data[item.name];
            });
            addContact();
        });
        let selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);
    </script>
@endsection
