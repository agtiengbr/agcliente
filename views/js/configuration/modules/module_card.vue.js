Vue.component('agcliente-module-card', {
    props: {
        module: {
            type: Object,
            default: function(){
                return {}
            }
        }
    },
    data: function(){
        return {
            versionsLoaded: false,
            detailsModal: false,
            downloadModal: false,
            showLicense: false,
            php_version: '',
            versions: [],
            isLoading: false,
            isLoadingModal: false
        }
    },
    mounted: function(){
        this.php_version = agcliente.php_version.match(/^(([0-9]*).([0-9]*)).*$/)[1];
    },
    computed: {
        buttons: function(){
            let ret = [];

            ret.push({
                text: 'Baixar',
                action: 'download'
            });

            ret.push({
                text: 'Detalhes',
                action: 'details'
            });

            if (this.module.in_shop) {
                if (this.module.usable_version && this.module.usable_version != this.module.version_in_use) {
                    ret.push({text: 'Atualizar', action: 'agdownload'});
                }

                //if (!this.module.is_authenticated) {
                //    ret.push({text: 'Licença', action: 'license'});
                //}

                if (this.module.installed) {
                    ret.push({text: 'Desinstalar', action: 'aguninstall'});

                    if(this.module.enabled) {
                        ret.push({text: 'Desativar', action: 'agdisable'});
                    } else {
                        ret.push({text: 'Ativar', action: 'agenable'});
                    }
                } else {
                    ret.push({text: 'Instalar', action: 'aginstall'});
                }
            }

            return ret;
        },
        authenticatedVersions: function(){
            return this.versions;
        }
    },
    template:
    `
        <div class="agcliente-module">

            <agcliente-loading v-if="isLoading" :size="50"></agcliente-loading>

            <agmodal v-if="detailsModal" @backdrop-clicked="detailsModal=false" class="details">
                <template slot="body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="cover"><img :src="module.image_url"/></div>
                        </div>

                        <div class="col-md-8 module-details">
                            <div>
                                <p class="h4">{{module.product_name}}</p>
                                <div class="short_description" v-html="module.short_description"></div><br>
                                <div class="price">A partir de {{ module.price }}</div>
                            </div>
                            <br>
                            <div class="changelog well">
                                Changelog:
                                <ul>
                                    <li v-for="version in versions">
                                        Versão {{ version.version_number }} - <span v-html="version.changelog"></span>
                                    </li>
                                </ul>
                            </div>

                            <div class="buttons">
                                <a class="btn btn-default" target="_blank" :href='module.link'>Saiba Mais</a>
                            </div>
                        </div>
                    </div>
                </template>
            </agmodal>

            <agmodal v-if="downloadModal" @backdrop-clicked="downloadModal=false" class="download-modal">
                <template slot="body">

                    <agcliente-loading v-if="isLoadingModal" :size="50"></agcliente-loading>

                    Escolha uma das versões para baixar:

                    <div class='module-versions'>
                        <div class='module-version' v-for="version in authenticatedVersions">
                            <dl class="dl-horizontal">
                                <dt>Número da Versão:</dt>
                                <dd>{{ version.version_number }}</dd>

                                <dt>Data de Lançamento:</dt>
                                <dd>{{ version.date_add }}</dd>

                                <dt>Versões PrestaShop:</dt>
                                <dd>{{ psVersions(version) }}</dd>

                                <dt>Versões Agcliente:</dt>
                                <dd>{{ agclienteVersions(version) }}</dd>
                                                                
                                <dt>Changelog</dt>
                                <dd><span v-html="version.changelog"></span></dd>
                            </dl>

                            <div class="buttons">
                                <a :href='version.urls[php_version]' class="btn btn-sm btn-default" :disabled="checkIfDownloadToShopDisabled(version)" :title="getPsDownloadTitle(version)" style="pointer-events: auto;" @click.prevent="downloadToShopClicked(version)">Baixar para a loja</a>
                                
                                <a :href='version.urls_ps[php_version]' class="btn btn-sm btn-default">Download</a>
                            </div>
                        </div>
                    </div>
                </template>
            </agmodal>

            <div class="row w-100">
                <div class="col-md-5">
                    <div>
                        <img class="card-image" :src="module.image_url" :alt="module.product_name" :title="module.product_name"/>
                    </div>
                </div>

                <div class="col-md-7 module-data">
                    <div class="module-name">
                        <p>{{module.product_name}}</p>
                    </div>

                    <div class="buttons">
                        <div>
                            <button v-for="button in buttons" class="btn btn-default" @click.prevent="clicked(button)">{{ button.text }}</button>
                        </div>
                    </div>

                    <div v-if="!this.showLicense" class="authentication-status">
                        <span v-for="flag in this.module.flags" class="badge text-bg-info">{{ flag.name }}</span>
                    </div>

                    <div v-else class="license authentication-status">
                    </div>
                </div>
            </div>
        </div>
    `,
    methods: {
        clicked: async function(button)
        {
            this.isLoading = true;

            if (button.action == 'download') {
                if (!this.versionsLoaded) {
                    await this.loadVersions();
                }
                this.downloadModal = true;
                this.isLoading = false;
                return;
            } else if (button.action == 'details') {
                if (!this.versionsLoaded) {
                    await this.loadVersions();
                }
                this.detailsModal = true;
                this.isLoading = false;
                return;
            } else if (button.action == 'license') {

                this.showLicense ? this.showLicense = false : this.showLicense = true;
                this.isLoading = false;
                return;
            }

            let url = new URL(location.href);
            let params = url.searchParams;

            params.set('action', button.action);
            params.set('agcliente_module', this.module.module_name);

            let r = await axios.get(url.toString()).then((data) => {
                if(data.data.success) {
                    if(button.action == 'agdisable') {
                        $.growl.notice({title: '', message: 'Módulo desativado com sucesso.'});
                        this.module.enabled = false;
                    }
                    if(button.action == 'aguninstall') {
                        $.growl.notice({title: '', message: 'Módulo desinstalado com sucesso.'});
                        this.module.installed = false;
                    }
                    if(button.action == 'aginstall') {
                        $.growl.notice({title: '', message: 'Módulo instalado com sucesso.'});
                        this.module.installed = true;
                    }
                    if(button.action == 'agenable') {
                        $.growl.notice({title: '', message: 'Módulo ativado com sucesso.'});
                        this.module.enabled = true;
                    }
                } else {
                    $.growl.error({title: '', message: 'Ocorreu um erro inesperado, tente novamente mais tarde.'});
                }
                this.isLoading = false;
            });
        },
        loadVersions: async function(){
            this.versions = [];
            this.versionsLoaded = true;
        },
        downloadToShopClicked: async function(version)
        {
            if (this.checkIfDownloadToShopDisabled(version)) {
                this.isLoading = false;
                this.isLoadingModal = false;
                return false;
            }

            this.isLoadingModal = true;

            let url = new URL(location.href);
            let params = url.searchParams;

            params.set('action', 'agdownload');
            params.set('agcliente_module', this.module.module_name);
            params.set('version_number', version.version_number);

            await axios.get(url.toString()).then((data) => {
                this.isLoadingModal = false;

                if (data.data.success) {
                    $.growl.notice({title: '', message: 'Download efetuado com sucesso.'});
                } else {
                    $.growl.error({title: '', message: 'Ocorreu um erro ao efetuar o download.'});
                }
            });
        },
        getPsDownloadTitle: function(version)
        {
            if (version.compatible == '0') {
                return  'Essa versão não é compatível com o seu PrestaShop ou com a versão do módulo AGTI Cliente existente na sua loja.';
            }

            if (this.module.version_in_use && compareVersions.compareVersions(version.version_number, this.module.version_in_use) <= 0) {
                return 'Você está utilizando uma versão mais recente do módulo em sua loja. Nós não suportamos downgrade, e o download de uma versão anterior pode fazer com que o módulo deixe de funcionar.';
            }

            return 'Clique aqui para baixar a versão ' + version.version_number + ' na sua loja.';
        },
        checkIfDownloadToShopDisabled: function(version)
        {
            return version.compatible == '0' || (this.module.version_in_use && compareVersions.compareVersions(version.version_number, this.module.version_in_use) <= 0);
        },
        sendLicense: async function()
        {
            // Licenciamento removido
        },
        agclienteVersions: function(version)
        {
            if (!version.agcliente_versions.min && version.agcliente_versions.max) {
                return 'Até ' + version.agcliente_versions.max;
            }

            if (version.agcliente_versions.min && !version.agcliente_versions.max) {
                return 'A partir de ' + version.agcliente_versions.min;
            }

            if (!version.agcliente_versions.min) {
                return 'Sem restrições.';
            }
            
            return `De ${version.agcliente_versions.min} a ${version.agcliente_versions.max}`;
        },
        psVersions: function(version)
        {
            if (!version.ps_versions.min && version.ps_versions.max) {
                return 'Até ' + version.agcliente_versions.max;
            }

            if (version.ps_versions.min && !version.ps_versions.max) {
                return 'A partir de ' + version.ps_versions.min;
            }

            if (!version.ps_versions.min) {
                return 'Sem restrições.';
            }
            
            return `De ${version.ps_versions.min} a ${version.ps_versions.max}`;
        }
    }
})
