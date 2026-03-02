@extends('layouts.main')
@section('title', 'Nota Fiscal de Entrada')

@section('content')

  <div class="col-12">
    <h3 class="header-page">Mural de avisos</h3>
  </div>
  <hr>

  <div class="accordion" id="accordionMain"></div>

@endsection

@section('scripts')

  <script>
    let index = 1;

    let array_data = [
      {
        title: "Atualização",
        body: [
          {
          'title': 'Mudança de layout',
          'description': 'Foi Realizado a troca do framework front-end (Novos formulários - telas)',
          },
          {
          'title': 'Seta de navegação',
          'description': 'Inclusão da seta de navegação nas MÓDULOS COMPRAS E ESTOQUE ( Transações: PN´s, Cadastro de Item)',
          },
          {
          'title': 'Pré visualização',
          'description': 'Inclusão da funcionalidade visulização das linhas ao clicar dos documentos nas telas de pesquisa no MÓDULO COMPRAS',
          },
          {
          'title': 'Modal IRF',
          'description': 'Inclusão da funcionalidade modal para Impostos Retidos na Fonte na tela de Nota Fiscal de Serviços',
          },
          {
          'title': 'Sicronização',
          'description': 'Sicronização com os adiantamentos de fornecedor com origem SAP B1',
          },
          {
          'title': 'Implementação de navegação lateral',
          'description': 'Implementação de funcionalidade navegação lateral nas telas do sistema',
          },
          {
          'title': 'Visualização rápida',
          'description': 'Implementação da funcionalidade visulização rápida para PNs e Itens nos MÒDULOS: COMPRA e EDTOQUE',
          },
          {
          'title': 'Consulta do CNPJ com RFB',
          'description': 'Implementação da funcionalidade de preecchimento rápido para: endereço, contato e cnae na tela de cadastro de PN',
          },
          {
          'title': 'Integração com portal suporte A2R',
          'description': 'Implementação da funcionalidade para abertura de chamado com portal de suporte da A2R (Restrito ao setor de TI do cliente) ',
          },
          {
          'title': 'DASHBOARD',
          'description': 'Implementação da funcionalidade dashboard para os MÓDULO: COMPRAS por centro de resultados',
          },
        ],
        version: 'PL0001',
        date: '03/04/2023'
      },
      {
        title: "Atualização",
        body: [
          {
          'title': 'Mudança no processo da sincronia da cotação com o SAP',
          'description': 'Após a atualização, o processo de enviar cotações para o SAP mudou. Deixou de ser enviado para o SAP apenas quando o pedido de compras fosse enviado para o SAP e, agora, assim que o botão "Atualizar" é clicado, a cotação já é enviada para o SAP',
          },
        ],
        version: 'PL0002',
        date: '12/04/2023'
      },
      {
        title: "Atualização",
        body: [
          {
          'title': 'Atualização do layout do modulo Compras',
          'description': 'Realizado melhoria do layout do modulo Compras e adição da barra superior de navegação entre documentos',
          },
        ],
        version: 'PL0003',
        date: '01/12/2023'
      },
    ];

    $.each(array_data, function(index, data){
      let accordion_item = $(`<div class="accordion-item mt-1"></div>`);

      let accordion_header = $(`<h2 class="accordion-header" id="heading-${index}">
                                  <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-${index}" aria-expanded="true" aria-controls="collapse-${index}">
                                    <span class="fw-bold">${data.title} - ${data.version}</span><span class="ms-3 text-muted">${data.date}</span>
                                  </button>
                                </h2>`);
      let accordion_body = $(`<div id="collapse-${index}" class="accordion-collapse collapse" aria-labelledby="heading-${index}" data-bs-parent="#accordion-${index}"></div>`);
      
      let accordion_body_item = $(`<div class="accordion-body"></div>`);
      $.each(data.body, function(ind, value){
        accordion_body_item.append($(`<p><strong>${ind+1}° ${value.title}:</strong> ${value.description}.</p>`))
      });
      
      $('#accordionMain').append(accordion_item);
      accordion_item.append(accordion_header);
      accordion_item.append(accordion_body);
      accordion_body.append(accordion_body_item);
    });

    
  </script>

@endsection
