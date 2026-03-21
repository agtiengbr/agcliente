Vue.component('agti-zipcodes-grid-zones', {
    props: {
        zone: {
            type: String,
            default: function(){
                return "0";
            }
        },
        row: {
            type: Object
        }
    },
    data: function(){
        return {
            dataZone: [],
            zones : [
                {
                    'label' : 'Nenhuma região selecionada.',
                    'name' : '0'
                },
                {
                    'label': 'Todo o Brasil',
                    'name' : 'brasil'
                },
                {
                    'label' : 'Norte',
                    'name' : 'norte'
                },
                {
                    'label' : 'Nordeste',
                    'name' : 'nordeste'
                },
                {
                    'label' : 'Centro Oeste',
                    'name' : 'centro-oeste'
                },
                {
                    'label' : 'Sudeste',
                    'name' : 'sudeste'
                },
                {
                    'label' : 'Sul',
                    'name' : 'sul'
                },
            ]
        }
    },
    mounted: function() {
        this.setZone();
    },
    template: 
    `
    <div>
        <select class="border-bottom form-control" v-model="dataZone">
            <option v-for="zone in zones" :value="zone.name">{{ zone.label }}</option>
        </select>
        <span class="custom-hidden">
            hidden
        </span>
    </div>
    `,
    methods: {
        setZone: function() {
            this.dataZone = this.zone
        }
    },
    watch: {
        dataZone: function(){
            this.$emit('changed', this.dataZone, this.row);
        },
        zone: function() {
            this.setZone();
        }
    }
})
