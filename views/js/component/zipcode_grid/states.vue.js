Vue.component('agti-zipcode-grid-states', {
    props: {
        uf: {
            type: String,
            default: '0'
        },
        row: {
            type: Object
        }
    },
    data: function(){
        return {
            dataUf: '',
            states: [
                {
                    name: 'Nenhum estado selecionado.',
                    code: 0
                },
                {
                    name: 'Acre',
                    code: 'AC'
                },
                {
                    name: 'Alagoas',
                    code: 'AL'
                },
                {
                    name: 'Amapá',
                    code: 'AP'
                },
                {
                    name: 'Amazonas',
                    code: 'AM'
                },
                {
                    name: 'Bahia',
                    code: 'BA'
                },
                {
                    name: 'Ceará',
                    code: 'CE'
                },
                {
                    name: 'Distrito Federal',
                    code: 'DF'
                },
                {
                    name: 'Espírito Santo',
                    code: 'ES'
                },
                {
                    name: 'Goiás',
                    code: 'GO'
                },
                {
                    name: 'Maranhão',
                    code: 'MA'
                },
                {
                    name: 'Mato Grosso',
                    code: 'MT'
                },
                {
                    name: 'Mato Grosso do Sul',
                    code: 'MS'
                },
                {
                    name: 'Minas Gerais',
                    code: 'MG'
                },
                {
                    name: 'Pará',
                    code: 'PA'
                },
                {
                    name: 'Paraíba',
                    code: 'PB'
                },
                {
                    name: 'Paraná',
                    code: 'PR'
                },
                {
                    name: 'Pernambuco',
                    code: 'PB'
                },
                {
                    name: 'Piauí',
                    code: 'PI'
                },
                {
                    name: 'Rio de Janeiro',
                    code: 'RJ'
                },
                {
                    name: 'Rio Grande do Norte',
                    code: 'RN'
                },
                {
                    name: 'Rio Grande do Sul',
                    code: 'RS'
                },
                {
                    name: 'Rondônia',
                    code: 'RO'
                },
                {
                    name: 'Roraima',
                    code: 'RR'
                },
                {
                    name: 'Santa Catarina',
                    code: 'SC'
                },
                {
                    name: 'São Paulo',
                    code: 'SP'
                },
                {
                    name: 'Sergipe',
                    code: 'SE'
                },
                {
                    name: 'Tocantins',
                    code: 'TO'
                }
            ]
        }
    },
    mounted: function() {
        this.setUf();
    },
    template:
    `
        <div>
            <select class="border-bottom form-control" v-model="dataUf" @change="changed">
                <option v-for="state in states" :value="state.code">{{ state.name }}</option>
            </select>
            <span class="btn-limpar" @click="reset()">
                Limpar
            </span>
        </div>
    `,
    methods: {
        changed: function(){
            this.$emit('changed', this.dataUf, this.row)
        },
        reset: function(){
            this.dataUf = '0';
            this.changed();
        },
        setUf: function() {
            this.dataUf = this.uf;
        }
    },
    watch: {
        uf: function() {
            this.setUf();
        }
    }
});
