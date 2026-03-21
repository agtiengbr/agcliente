window.addEventListener('load', function(){
    $(function(){
        new Vue({
            el: '#agclienteConfigurationApp',
            data: {
                tabs: [
                    {
                        text: "Configurações",
                        icon: "icon icon-cogs",
                        content: {
                            component: 'agcliente-tab-config',
                            props: {
                                url_workers: agcliente.urls.worker,
                                worker_running: agcliente.worker_running
                            }
                        }
                    }
                ]
            }
        });
    });
});
