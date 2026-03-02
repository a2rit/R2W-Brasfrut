@extends('layouts.main')
@section('title', 'NFC-e Erros')

@section('content')
  <div class="col-12">
    <h3 class="header-page">Erros de Sincronização</h3>
  </div>
  <hr>


  <div class="row" id="app2">
    <div class="col-md-8 form-group">
      <label>Clique ao lado e selecione o intervalor entre datas:</label>
      {{-- <date-picker lang="pt-br" v-model="data" format="DD-MM-YYYY" range></date-picker> --}}
      <date-range-picker ref="picker" :locale-data="dateLocale" :max-date="moment().toDate()" :auto-apply="true"
        v-model="data2" :ranges="false" opens="right">
        <template v-slot:input="picker" style="min-width: 350px;">
          @{{ picker.startDate ? moment(picker.startDate).format('DD-MM-YYYY') : 'Selecione a data' }}
          @{{ picker.endDate ? ' - ' + moment(picker.endDate).format('DD-MM-YYYY') : '' }}
        </template>
      </date-range-picker>
      <button class="btn btn-info" @click="clearDate()">Limpar</button>
    </div>
    <div class="col-md-4 mb-2">
        <a href="{{ route('erros.force-sync') }}"><button class="btn btn-warning float-end">Forçar Sincronismo</button></a>
        <button class="btn btn-success float-end me-1" @click="toExcel()">Excel</button>
    </div>
    <div class="col-md-12">
      <vue-good-table ref="table" @on-page-change="onPageChange" @on-per-page-change="onPageChange"
        @on-sort-change="onSortChange" @on-column-filter="onFilterChange" :columns="columns" :rows="filteredRows"
        :pagination-options="paginationOptions" :sort-options="sortOptions">
      </vue-good-table>
    </div>
  </div>
@endsection

@section('scripts')
  <script type="application/javascript">
        let errors = @json($erros);
        let app = new Vue({
            el: '#app2',
            created: function() {
                this.paginationOptions.setCurrentPage = parseInt(this.$cookie.get('errors_currentPage')) || 1;
                this.paginationOptions.perPage = parseInt(this.$cookie.get('errors_currentPerPage')) || 10;

                this.sortOptions.initialSortBy.type = this.$cookie.get('errors_sortType') || 'asc';
                this.sortOptions.initialSortBy.field = this.$cookie.get('errors_columnIndex') || 'doc_date';

                let filters = JSON.parse(this.$cookie.get('errors_columnFilters')) || {};
                _.map(this.columns, function (value, key) {
                    _.map(filters, function (_value, _key) {
                        if(_key === value.field) {
                            value.filterOptions.filterValue = _value;
                        }
                    })
                });

                if(this.$cookie.get('errors_date')) {
                    let date = new Date(this.$cookie.get('errors_date'));
                    if(!isNaN(date.getDate())) {
                        this.data = date;
                    }
                }
                if(this.$cookie.get('errors_date2')) {
                    this.data2 = JSON.parse(this.$cookie.get('errors_date2'));
                }
            },
            data: {
                data: null,
                data2: {},
                pvs: @json($pvs),
                sortOptions: {
                    enabled: true,
                    initialSortBy: {field: 'doc_date', type: 'desc'}
                },
                rows: errors,
                paginationOptions: {
                    enabled: true,
                    mode: 'records',
                    position: 'top',
                    perPageDropdown: [10, 20, 30],
                    dropdownAllowAll: false,
                    nextLabel: 'próximo',
                    prevLabel: 'anterior',
                    rowsPerPageLabel: 'Linhas por página',
                    ofLabel: 'de',
                    pageLabel: 'página', // for 'pages' mode
                },
                columns: [
                    {
                        label: 'Id',
                        field: 'id',
                        type: 'number'
                    },
                    {
                        label: 'N° NFC-e',
                        field: 'numero_nfce'
                    },
                    {
                        label: 'N° Pedido venda',
                        field: 'pedido_venda'
                    },
                    {
                        label: 'Ponto de Venda',
                        field: 'pv_id',
                        formatFn(value) {
                            return _.find(@json($pvs), {value: value}).text;
                        },
                        filterOptions: {
                            enabled: true, // enable filter for this column
                            placeholder: 'Todos', // placeholder for filter input
                            filterDropdownItems: @json($pvs), // dropdown (with selected values) instead of text input
                        },
                    },
                    {
                        label: 'Mensagem',
                        field: 'mensagem'
                    },
                    {
                        label: 'Tipo',
                        field: 'tipo',
                        filterOptions: {
                            enabled: true, // enable filter for this column
                            placeholder: 'Todos', // placeholder for filter input
                            filterDropdownItems: ['Erro', 'Advertência'], // dropdown (with selected values) instead of text input
                        },
                    },
                    {
                        label: 'Modelo',
                        field: 'tipo_modelo',
                        filterOptions: {
                            enabled: true, // enable filter for this column
                            placeholder: 'Todos', // placeholder for filter input
                            filterDropdownItems: @json($modelos), // dropdown (with selected values) instead of text input
                        },
                        formatFn(value) {
                            return _.find(@json($modelos), {value: value}).text;
                        }
                    },
                    {
                        label: 'Tipo',
                        field: 'intern_consumption_type',
                        formatFn(value) {
                            return _.find(@json($internConsumptionTypes), {value: value})?.text;
                        },
                        filterOptions: {
                            enabled: true, // enable filter for this column
                            placeholder: 'Todos', // placeholder for filter input
                            filterDropdownItems: @json($internConsumptionTypes), // dropdown (with selected values) instead of text input
                        },
                    },
                    {
                        label: 'Data da NFCe',
                        field: 'doc_date',
                        type: 'date',
                        dateInputFormat: 'yyyy-MM-dd HH:mm:ss.SSS', // expects 2018-03-16
                        dateOutputFormat: 'dd-MM-yyyy', // outputs Mar 16th 2018
                    },
                    {
                        label: 'Ver',
                        html: true,
                        sortable: false,
                        field: function (rowObj) {
                            return `<a href="${rowObj.ver_url}"><button class="btn btn-success">Ver</button></a>`
                        }
                    }
                ],
                dateLocale: {
                    direction: 'ltr',
                    format: 'dd-mm-yyyy',
                    separator: ' - ',
                    applyLabel: 'Aplicar',
                    cancelLabel: 'Cancelar',
                    weekLabel: 'W',
                    customRangeLabel: 'Intervalo',
                    daysOfWeek: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
                    monthNames: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                    firstDay: 0
                }
            },
            computed: {
                filteredRows: function () {
                    this.$cookie.set('errors_columnFilters', JSON.stringify(this.data2), 1);
                    this.$cookie.set('errors_date', this.data, 1);
                    this.$cookie.set('errors_date2', JSON.stringify(this.data2), 1);
                    //this.$cookie.set('errors_currentPage', this.paginationOptions.setCurrentPage, 1);
                    if(this.$refs.table) {
                        this.$cookie.set('errors_currentPage', this.$refs.table.$data.currentPage, 1);
                    }
                    //if(!this.data) {
                    if(!this.data2) {
                        return this.rows;
                    }

                    return _.filter(this.rows, (n) => {
                        //return moment(n.doc_date).format('DD-MM-YYYY') === this.dataFormatada;
                        if(!this.data2.startDate || !this.data2.endDate) {
                            return true;
                        }
                        return moment(n.doc_date).isBetween(this.data2.startDate, this.data2.endDate, 'day', '[]');
                    });
                },
                dataFormatada() {
                    return moment(this.data).format('DD-MM-YYYY');
                },
                moment() {
                    return window.moment;
                }
            },
            methods: {
                onPageChange: function (pagination) {
                    if(!this.$refs.table) {
                        console.log('ignored setCurrentPage');
                        return;
                    }
                    this.$cookie.set('errors_currentPage', pagination.currentPage, 1);
                    this.$cookie.set('errors_currentPerPage', pagination.currentPerPage, 1);
                },
                onSortChange: function (sort) {
                    this.$cookie.set('errors_columnIndex', sort[0].field, 1);
                    this.$cookie.set('errors_sortType', sort[0].type, 1);
                },
                onFilterChange: function (filter) {
                    this.$cookie.set('errors_columnFilters', JSON.stringify(filter.columnFilters), 1);
                },
                clearDate() {
                    this.data2.startDate = null;
                    this.data2.endDate = null;
                },
                forceSync() {
                    axios.post('{{route('erros.force-sync')}}')
                },
                toExcel() {
                    let filters = JSON.parse(this.$cookie.get('errors_columnFilters'));
                    Object.assign(filters, {'startDate': moment(this.data2.startDate).format('YYYY-MM-DD'), 'endDate': moment(this.data2.endDate).format('YYYY-MM-DD')});
                    let form = $('<form method="POST" action="{{route('erros.toExcel')}}"></form>');
                    $.each(filters, function(index, value){
                        form.append(`<input type="hidden" name="${index}" value="${value}">`);
                    });
                    form.append(`<input type="hidden" name="_token" value="{{ csrf_token() }}">`);
                    $('body').append(form)
                    form.submit();
                }
            }
        });
    </script>
@endsection
