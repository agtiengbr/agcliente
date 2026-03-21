document.addEventListener('DOMContentLoaded', function(){
    $(document).on('click', "#tab_maintenance .update_database", (e) => { e.preventDefault(); ConfirmDecision('updateModuleTables', ManageMaintanceRequests); });
    $(document).on('click', "#tab_maintenance .clean_module_db", (e) => { e.preventDefault(); ConfirmDecision('JustCleanModuleTables', ManageMaintanceRequests); });
    $(document).on('click', "#tab_maintenance .delete_tables", (e) => { e.preventDefault(); ConfirmDecision('RemoveModuleTables', ManageMaintanceRequests); });
    $(document).on('click', "#tab_maintenance .reset_configs", (e) => { e.preventDefault(); ConfirmDecision('ResetConfigs', ManageMaintanceRequests); });
    $(document).on('click', "#tab_maintenance .remake_menus", (e) => { e.preventDefault(); ConfirmDecision('RemakeMenus', ManageMaintanceRequests); });
    $(document).on('click', "#tab_maintenance .remake_workers", (e) => { e.preventDefault(); ConfirmDecision('RemakeWorkers', ManageMaintanceRequests); });
    $(document).on('click', "#tab_maintenance .reset_hooks", (e) => { e.preventDefault(); ConfirmDecision('ResetHooks', ManageMaintanceRequests); });

    function ConfirmDecision(request_type, reqFunction) {
        console.log(request_type);
        var resp = confirm('Tem certeza que deseja executar essa ação?');

        if(resp == true) {
            reqFunction(request_type);
        }
    }

    function ManageMaintanceRequests(request_type = '') {
        if(request_type != '' && request_type != undefined) {
            let endpointUrl;

            // Parse the major version from ps_version (e.g., "9.4.3.1" -> 9)
            let majorVersion = parseInt(ps_version.split('.')[0]);

            if (majorVersion >= 9) { // Use major version to determine logic
                // Construct the endpoint URL dynamically for PrestaShop 9.0 and above
                let currentUrl = new URL(window.location.href);
                let pathParts = currentUrl.pathname.split('/');
                pathParts[pathParts.length - 1] = 'agcliente';
                endpointUrl = currentUrl.origin + pathParts.join('/') + currentUrl.search;
            } else {
                // Use the existing logic for earlier versions
                endpointUrl = 'index.php';
            }

            $.ajax({
                url: endpointUrl,
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {
                    'ajax': true,
                    'controller': 'AdminModules',
                    'configure': 'agcliente',
                    'action': 'MaintanceOptions',
                    'request_type': request_type,
                    'extra': {
                        'module': module,
                    },
                    'token' : token,
                },
            })
            .then(function(data){           
                if(data.errors.length === 0) {
                    message = '<div class="alert alert-success" role="alert" id="agcliente_message">Ação concluida</div>';
                    $.growl.notice({ 'title': 'Sucesso', 'message': message });

                    if(request_type == 'ResetConfigs') {
                        setTimeout(window.location.href = window.location.href, 1000);
                    }
                } else {
                    message = '<div class="alert alert-danger" role="alert" id="agcliente_message">Ocorreu ao executar a ação, confira os registros da loja.</div>';
                    $.growl.error({ 'title': 'Error', 'message': message});
                }
            })
            .fail(function(data){
                message = `<div class="alert alert-danger" role="alert" id="agcliente_message">
                Ocorreu  ao executar a ação, confira os registros da loja.
                </div>`;
                $.growl.error({ 'title': 'Error', 'message': message});
            });
        } else {
            message = `<div class="alert alert-danger" role="alert" id="agcliente_message">
                Nenhuma ação enviada
            </div>`;
            $.growl.error({ 'title': 'Error', 'message': message});
        }
    }
});