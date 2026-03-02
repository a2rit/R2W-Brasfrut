let columnsToFilter_SearchTopNav;

window.fillTopNav = (data) => {
    columnsToFilter_SearchTopNav = data.searchFields;
    fillUrlsTopNav(data.urls);
    renderSearchModal();
}


function fillUrlsTopNav (data){
    $.each(data, function(key, value){
        if(key === 'print_urls' && Object.keys(value).length){
            let divParent = $('#dropdown_print_div');
            let divDropdownList = divParent.find('.dropdown-menu');
            $.each(value, function(key, value){
                divDropdownList.append(
                    $(
                        `<li>
                            <a class="dropdown-item" href="${value}"
                                target="_blank">${key}
                            </a>
                        </li>`
                    )
                );
            });
            divParent.removeClass('d-none');
        }else if(key === 'send_to_email_element_attributes' && Object.keys(value).length){
            let element = $('#send-to-email');
            $.each(value.content_attributes, function(key, value){
                element.attr(key, value);
            });
            element.removeClass('d-none');
        }else{
            $(`#${key}`).attr('href', value).removeClass('d-none');
        }
    });

    $("#refresh_page_url").removeClass('d-none');
    $("#search-document-button").removeClass('d-none');
}

function renderSearchModal(){
    $('body').append($(`
        <div class="modal fade" id="searchDocumentTopNavModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Pesquisa de documentos</h5>
                        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-coreui-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
    `))

    $('#searchDocumentTopNavModal .modal-body').append($(`
        <div class="table-responsive w-100 m-0 p-0">
            <table id="searchDocumentsTable" class="table table-hover table-bordered" style="width:100%">
                <thead class="table-secondary">
                    <tr></tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    `));

    var columns = [];
    let functions = {
        'renderFormatedDate': renderFormatedDate,
        'renderFormatedMoney': renderFormatedMoney,
        'renderRedirectButton': renderRedirectButton,
    };

    $.each(columnsToFilter_SearchTopNav.fields, function(index, value){
        if(value.list){
            columns.push({
                title: value.title,
                name: value.fieldName,
                data: value.fieldName,
                render: functions[value.render]
            });
        }
    });

    setTimeout(function(){
        $('#searchDocumentsTable').DataTable({
            columnDefs: [{
                "defaultContent": ""
            }],
            ajax: {
                url: columnsToFilter_SearchTopNav.form_url,
                type: "GET",
                data: function(d){
                    d.fields = columnsToFilter_SearchTopNav.fields
                },
            },
            columns: columns,
            language: dataTablesPtBr,
            paging: false,
            processing: true,
            serverSide: true,
            destroy: true,
        });
    }, 2000);
}

function renderRedirectButton(value, display, values){
    return `<center>
                <a class='btn btn-sm ${values.COLOR_STATUS}' href='${columnsToFilter_SearchTopNav.read_document_url}/${values.id}'>
                    ${value}
                </a>
            </center>`;
}