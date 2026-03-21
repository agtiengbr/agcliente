Vue.component('agti-zipcode-grid-cities', {
    props: {
        row: [],
        city: {
            default: ' '
        },
        apiUrl: ''
    },
    data: function(){
        return {
            cities: [],
            cityData: '',
            data_api_url: ''
        }
    },
    mounted: function() {
        this.apiUrl ? this.data_api_url = this.apiUrl : this.data_api_url = location.href;
        this.cityData = this.city;
    },
    template: 
    `
    <div>
        <span>
            Cidade: 
            <span v-if="this.cityData.city && this.cityData.city !== 'undefined' && this.cityData.city !== '0'">
                {{ this.cityData.city }}
            </span>
        </span>
        <span class="border-bottom">
            <agcliente-form-autocomplete @change="search" component="agti-zipcode-grid-city-autocomplete" :objects="cities" :listeners="{selected: selected}"></agcliente-form-autocomplete>
        </span>    
    </div>
        `,
    watch: {
        city: function() {
            this.cityData = this.city;
        }
    },
    methods: {
        search: async function(name){
            if(name) {
                let state = '';
                this.row.uf ? state = this.row.uf : this.row.state ? state = this.row.state : state = '';

                if(state == 'undefined') {
                    state = ''
                }

                let data = await axios.get(`${this.data_api_url}&searchCityByName&uf=${state}&name=${name}`);
                
                if(data.data) {
                    this.cities = data.data;
                } else {
                    this.cities = [];
                }
            } else {
                this.cities = [];
            }
        },
        selected: function(city){
            this.cities = [];
            this.row.city = city;
            this.$emit('selected', city, this.row);
        }
    }
})
