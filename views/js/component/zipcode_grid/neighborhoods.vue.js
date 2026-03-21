Vue.component('agti-zipcode-grid-neighborhoods', {
    props: {
        row: [],
        neighborhood: {
            default: ' '
        },
        city: [],
        state: '',
        apiUrl: ''
    },
    data: function(){
        return {
            neighborhoods: [],
            neighborhoodData: '',
            data_api_url: ''
        }
    },
    template: 
    `
    <div v-if="this.city && this.city.city && this.city.city !== '0' && (this.row.uf || this.city.state || this.state)">
        <span>
            Bairro: 
            <span v-if="this.neighborhoodData.neighborhood && this.neighborhoodData.neighborhood !== '0' && this.neighborhoodData.neighborhood !== 'undefined'">
                {{ this.neighborhoodData.neighborhood }}
            </span>
        </span>
        <span class="border-bottom">
            <agcliente-form-autocomplete @change="search" component="agti-zipcode-grid-neighborhood-autocomplete" :objects="neighborhoods" :listeners="{selected: selected}"></agcliente-form-autocomplete>
        </span>
    </div>
    <div v-else>
        <div>
            <span>
                Bairro: 
            </span>
            <div>
                <input type="text" class="border-bottom form-control" disabled placeholder="Selecione uma cidade">
                <div class="choose"></div>
            </div>
        </div>
    </div>
        `,
    watch: {
        neighborhood: function() {
            this.neighborhoodData = this.neighborhood;
        }
    },
    mounted: function() {
        this.apiUrl ? this.data_api_url = this.apiUrl : this.data_api_url = location.href;
        this.neighborhoodData = this.neighborhood;
    },
    methods: {
        search: async function(neighborhood){
            let data = await axios.get(`${this.data_api_url}&searchNeighborhoodByName&uf=${this.city.state ? this.city.state : this.state}&city=${this.city.city}&neighborhood=${neighborhood}`);
            this.neighborhoods = data.data;
        },
        selected: function(neighborhood, isCleaning){
            this.neighborhoods = [];
            this.row.neighborhood = neighborhood;
            this.$emit('selected', neighborhood, this.row, isCleaning);
        }
    }
})
