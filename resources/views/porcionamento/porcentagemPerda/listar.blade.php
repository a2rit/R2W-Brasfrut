@extends('layouts.main')
@section('title', 'Porcionamento')

@section('content')
  <!-- Page Heading -->
  <div class="row">
    <div class="col-md-10">
      <h3 class="header-page">Porcentagens de perda</h3>
    </div>
    <div class="col-md-2">
      <div class="dropdown float-end">
        <a href="{{ route('porcionamento.porcentagem-perda') }}" class="btn btn-primary" type="button">Adicionar</a>
      </div>
    </div>
  </div>
  <hr>

  <div class="row" id="app">
    <div class="col-md-12">
      <vue-good-table ref="table" :columns="columns" :rows="rows" :search-options="searchOptions"
        :pagination-options="paginationOptions"></vue-good-table>
    </div>
  </div>
@endsection

@section('scripts')
  <script>
    let app = new Vue({
      el: '#app',
      data: {
        searchOptions: {
          enabled: true,
          placeholder: 'Pesquise pelo nome ou código do item...',
        },
        paginationOptions: {
          enabled: true,
          mode: 'records',
          perPage: 10,
          position: 'top',
          perPageDropdown: [10, 20, 30, 50],
          dropdownAllowAll: true,
          nextLabel: 'próxmo',
          prevLabel: 'anterior',
          rowsPerPageLabel: 'Linhas por página',
          ofLabel: 'de',
          pageLabel: 'página', // for 'pages' mode
          allLabel: 'Todos',
        },
        columns: [{
            label: 'Cógido',
            field: 'codigo',
          },
          {
            label: 'Nome',
            field: function(row) {
              //return row.item_sap ? row.item_sap.ItemName : '';
              return row.item_name;
            }
          },
          {
            label: 'Porcentagem base',
            field: function(row) {
              return `${row.porcentagem_base} %`
            },
          },
          {
            label: 'Porcentagem aceita',
            field: function(row) {
              return `${row.porcentagem_aceita} %`
            },
          },
          {
            label: 'Comentário',
            field: 'comentario',
          },
          {
            label: "Excluir",
            html: true,
            field: function(row) {
              return `<button class="btn btn-primary btn-sm" onclick="if(confirm('Editar?')) {window.location.href='{{ route('porcionamento.listar-editar') }}/${row.codigo}'}">Editar</button>
                            <button class="btn btn-danger btn-sm" onclick="if(confirm('Excluir?')) {window.location.href='${row.delete_url}'}" style="padding-left: 5%;">Excluir</button>`;

            }
          }
        ],
        rows: @json($itens)
      },
      methods: {},
      mounted() {
        @if (request()->get('search'))
          this.$refs.table.globalSearchTerm = '{{ request()->get('search') }}';
        @endif
      }
    });
  </script>
@endsection
