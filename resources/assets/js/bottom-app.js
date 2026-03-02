

require('./bootstrap-select');
require('./topNavigationMenu');
require('ajax-bootstrap-select');
require('ajax-bootstrap-select/dist/js/locale/ajax-bootstrap-select.pt-BR');



const tooltipTriggerList = $('[data-coreui-toggle="tooltip"]');
[...tooltipTriggerList].map(tooltipTriggerEl => new coreui.Tooltip(tooltipTriggerEl));

const slider = document.querySelector('.table-responsive');
if(slider){
    let isDown = false;
    let startX;
    let scrollLeft;
    
    slider.addEventListener('mousedown', (e) => {
        isDown = true;
        slider.classList.add('active');
        startX = e.pageX - slider.offsetLeft;
        scrollLeft = slider.scrollLeft;
    
        $('.table-responsive').css('cursor', 'grabbing');
    });
    
    slider.addEventListener('mouseleave', () => {
        isDown = false;
        slider.classList.remove('active');
    });
    
    slider.addEventListener('mouseup', () => {
        isDown = false;
        slider.classList.remove('active');
        $('.table-responsive').css('cursor', 'pointer');
    });
    
    slider.addEventListener('mousemove', (e) => {
        if(!isDown) return;
        e.preventDefault();
        
        const x = e.pageX - slider.offsetLeft;
        const walk = (x - startX) * 3; //scroll-fast
        slider.scrollLeft = scrollLeft - walk;
    });
}

window.getAjaxSelectPickerOptions = (url) => {
    return {
        ajax: {
            url: url,
            type: 'get',
            dataType: 'json',
            // Use "\{\{\{q}}}" as a placeholder and Ajax Bootstrap Select will
            // automatically replace it with the value of the search query.
            data: {
                q: '\{\{\{q}}}'
            }
        },
        locale: {
            emptyTitle: 'Selecione'
        },
        log: 0,
        preprocessData: function (data) {
            var i, l = data.length, array = [];
            if (l) {
                for (i = 0; i < l; i++) {
                    var name = data[i].name;
                    var maxLength = 70;
                    if (name.length > maxLength) {
                        name = name.substr(0, maxLength) + '...';
                    }
                    array.push($.extend(true, data[i], {
                        text: name,
                        value: data[i].value,
                        data: {
                            subtext: ''//data[i].name
                        }
                    }));
                }
            }
            // You must always return a valid array when processing data. The
            // data argument passed is a clone and cannot be modified directly.
            return array;
        }
    }
}
window.waitingDialog = require('bootstrap-waitingfor');

// var selectpickerIsClicked = false;

// $(function () {
//   $('.dropdown-menu').on('click', function (e) {
//     if ($(e.target).closest('.bootstrap-select.open').is(':visible') || $(e.target).closest('.btn.dropdown-toggle.btn-default').is(':visible')) {
//       selectpickerIsClicked = true;
//     }
//   });

//   $('.dropdown-menu').on('hide.coreui.dropdown', event => {
//     console.log(123);
//     if(selectpickerIsClicked){
//         event.stopPropagation();
//         selectpickerIsClicked = false;
//     };
//   })
// });



window.renderFormatedDate = (value) => {
    return dateFormat(value, 'UTC:dd/mm/yyyy');
}

window.renderFormatedMoney = (valor) => { //2 decimal cases
    return new Intl.NumberFormat('pt-BR', {style: "currency", currency: 'BRL', maximumSignificantDigits: 4}).format(valor);
}

window.renderFormatedQuantity = (valor) => {
    return new Intl.NumberFormat('pt-BR').format(valor);
}

window.roundNumber = (value) => {
    return Math.round(value * 100) / 100;
}