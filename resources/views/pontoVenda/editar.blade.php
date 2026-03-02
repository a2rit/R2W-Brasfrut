@extends('pontoVenda.criar')

@section('scripts')
<script type="application/javascript">

    var id = $("<input>")
            .attr("type", "hidden")
            .attr("name", "id").val("{{$pv->id}}");

    $('#cadastro').append($(id));

    $('#nome').val('{{$pv->nome}}');
    $('#vendedor').val('{{$pv->vendedor}}');
    $('#cliente').val('{{$pv->cliente}}');
    $('#modelo_nf').val('{{$pv->modelo_nf}}');
    $('#regra_distribuicao').val('{{$pv->regra_distribuicao}}');
    $('#regra_distribuicao_ov').val('{{$pv->regra_distribuicao_ov}}');
    $('#codigo_imposto').val('{{$pv->codigo_imposto}}');
    $('#codigo_imposto_ov').val('{{$pv->codigo_imposto_ov}}');
    $('#utilizacao').val('{{$pv->utilizacao}}');
    $('#pasta_xml').val('{{addslashes($pv->pasta_xml)}}');
    $('#pasta_xml_contingencia').val('{{addslashes($pv->pasta_xml_contingencia)}}');
    $('#projeto').val('{{$pv->projeto}}');
    $('#projeto_ov').val('{{$pv->projeto_ov}}');
    $('#codigo_ov').val('{{$pv->codigo_ov}}');
    $('#conta_dinheiro').val('{{ $pv->conta_dinheiro }}');
    $('#deposito').val('{{ $pv->deposito }}');
    $('#deposito_servico').val('{{ $pv->deposito_servico }}');
    $('#grupo_servico').val('{{ $pv->grupo_servico }}');
    $('#conta_troco').val('{{ $pv->conta_troco }}');
    $('#conta_cheque').val('{{ $pv->conta_cheque }}');
    $('#serie').val('{{ $pv->serie }}');
    $('#item_gorjeta_sap').val('{{ $pv->item_gorjeta_sap }}');
    $('#item_gorjeta_colibri').val('{{ $pv->item_gorjeta_colibri }}');
    $('#conta_gorjeta_credito').val('{{ $pv->conta_gorjeta_credito }}');
    $('#conta_gorjeta_debito').val('{{ $pv->conta_gorjeta_debito }}');
    $('#conta_pix').val('{{ $pv->conta_pix }}');


    // {{--$('#ci-projeto').val('{{$pv->ci_config["projeto"]}}');--}}
    // {{--$('#ci-utilizacao').val('{{$pv->ci_config["utilizacao"]}}');--}}
    // {{--$('#ci-usuarios').val({!! json_encode($pv->ci_config["usuarios"]) !!});--}}
    // {{--$('#ci-deposito').val('{{$pv->ci_config["deposito"]}}');--}}
    // {{--$('#ci-regra_distribuicao').val('{{$pv->ci_config["regra_distribuicao"]}}');--}}
    // {{--$('#ci-cliente').val('{{$pv->ci_config["cliente"]}}');--}}
    $('#intern_consumption-utilization').val('{{@$pv->ci_config["utilization"]}}');
    $('#intern_consumption-deposit').val('{{@$pv->ci_config["deposit"]}}');
    $('#intern_consumption-card_code').val('{{@$pv->ci_config["card_code"]}}');
    $('#intern_consumption-price_list').val('{{@$pv->ci_config["price_list"]}}');
    $('#intern_consumption-tax_code').val('{{@$pv->ci_config["tax_code"]}}');
    $('#intern_consumption-seller_code').val('{{@$pv->ci_config["seller_code"]}}');
    $('#printer_ip').val('{{@$pv->ci_config["printer_ip"]}}');
    $('#printer_port').val('{{$pv->ci_config["printer_port"] ?? 9100}}');

    let selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);
    selectpicker.selectpicker('destroy');
    selectpicker.selectpicker(selectpickerConfig).selectpicker('render');
    selectpicker.filter('.with-ajax-customer')
            .ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('partners.get.all') }}"));

    function adicionarFormaPag() {
        var valor = $("#valor").val();
        var chave = $("#chave_colibri").val();
        $.post("{{route("pv.addFormaPag")}}", {valor: valor, chave: chave, pv_id: "{{$pv->id}}", _token: "{{csrf_token()}}"}, function (data) {
            if(data.success === true){
                window.location.reload();
            }
        });
    }

</script>
@endsection
