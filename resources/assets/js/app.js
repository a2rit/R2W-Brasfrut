/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.coreui =  require('./coreui.bundle.js');
require('chart.js');

require('./main');
require('./colors');
require('./popovers');
require('./toasts');
require('./tooltips');

require('./daterangerpicker.min');
require('bootstrap-datepicker');
require('bootstrap-datepicker/dist/locales/bootstrap-datepicker.pt-BR.min');
// require('datatables.net-bs');
// require('datatables.net-responsive-bs');
window.dateFormat = require('dateformat');
require('./jquery.autocomplete');
require('jquery-mask-plugin');
require('jquery-maskmoney/src/jquery.maskMoney');
require('sweetalert');
require('./datatables');
require('./fixedColumnDatatable');
require('jszip');
require('pdfmake');
require('datatables.net-buttons-dt');
require('datatables.net-buttons/js/buttons.html5.js');
// require( 'datatables.net-fixedcolumns-dt' );
// require( 'datatables.net-fixedheader-dt' );
window.moment = require('moment');

window.FroalaEditor = require('froala-editor');
import 'froala-editor/js/languages/pt_br.js';
import 'froala-editor/js/plugins/image.min';
import 'froala-editor/js/plugins/image_manager.min';
import 'froala-editor/js/plugins/code_view.min';
import 'froala-editor/js/third_party/image_tui.min.js';
import 'froala-editor/js/plugins/line_height.min.js';
import 'froala-editor/js/plugins/font_size.min';
import 'froala-editor/js/plugins/font_family.min';
import 'froala-editor/js/plugins/emoticons.min';
import 'froala-editor/js/plugins/draggable.min';
import 'froala-editor/js/plugins/help.min';

// globais, qual a melhor maneira?

import {validarCpfCnpj} from "./cpfCnpjfunctions";
import VueGoodTablePlugin from 'vue-good-table';
// import the styles
import 'vue-good-table/dist/vue-good-table.css'
import VueCookie from 'vue-cookie'
import 'vue-select/dist/vue-select.css';
import DatePicker from 'vue2-datepicker';
import DateRangePicker from 'vue2-daterange-picker';
import 'vue2-daterange-picker/dist/vue2-daterange-picker.css';

window.daysOfWeekNames = [
    'Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'
];

window.monthNames = [
    'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho',
    'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
];

window.monthNamesShort = [
    'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'
];

DatePicker.fecha.i18n.monthNames = [
    'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho',
    'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
];
DatePicker.fecha.i18n.monthNamesShort = [
    'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'
];

window.daterangepickerConfig = {
    locale: {
        format: 'DD/MM/YYYY',
        cancelLabel: "Limpar",
        applyLabel: "Aplicar",
        cancelLabel: "Cancelar",
        fromLabel: "De",
        toLabel: "Até",
        customRangeLabel: "Customizado",
        weekLabel: "Semana",
        daysOfWeek: daysOfWeekNames,
        monthNames: monthNames,
      },
      autoUpdateInput: false,
};

window.validarCpfCnpj = validarCpfCnpj;
window.dataTablesPtBr = require('./dataTablesPtBr.lang').dataTablesPtBr;

window.Vue = require('vue');

Vue.use(DatePicker);
Vue.use(VueGoodTablePlugin);
Vue.component('v-select', require('vue-select').default);
Vue.component('date-picker2', require('./components/DatePicker2'));
Vue.use(require('vue-numeric').default);
Vue.use(require('bootstrap-vue'));

Vue.component('date-range-picker', DateRangePicker);

// Tell Vue to use the plugin
Vue.use(VueCookie);


/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

/*Vue.component('example-component', require('./components/ExampleComponent.vue'));

const app = new Vue({
    el: '#app'
});*/
