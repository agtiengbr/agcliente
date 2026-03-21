Vue.component('agcliente-tab-config', {
    props: ['worker_running', 'url_workers'],
    template:`
        <div class="panel">
            <div class="alert alert-warning" v-if="!worker_running">
                <p>Para o correto funcionamento dos módulos, por favor configure uma tarefa CRON para a seguinte URL, com periodicidade mínima de 15 minutos:
                <strong>{{ url_workers }}</strong></p>
                <p v-if="maintenance-mode">Como a sua loja está com o modo de manutenção ligado, será preciso adicionar o IP do seu próprio servidor aos IPs de Manutenção do PrestaShop, ou o acionamento não funcionará.</p>
            </div>
            <p class="alert alert-success" v-else>
                Detectamos que a tarefa CRON do módulo está sendo acionada dentro da periodicidade desejada :)
            </p>
        </div>
    `,
    computed: {
    }
});
