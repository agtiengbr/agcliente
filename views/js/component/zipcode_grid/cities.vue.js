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
            data_api_url: '',
            searchTimer: null,
            searchRequest: null,
            searchRequestId: 0
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
        search: function(name){
            if (this.searchTimer !== null) {
                clearTimeout(this.searchTimer);
                this.searchTimer = null;
            }

            if (this.searchRequest !== null) {
                this.searchRequest.cancel('Busca substituída por uma consulta mais recente.');
                this.searchRequest = null;
            }

            const requestId = ++this.searchRequestId;

            if (!name) {
                this.cities = [];
                return;
            }

            this.searchTimer = setTimeout(async () => {
                this.searchTimer = null;

                let state = '';
                this.row.uf ? state = this.row.uf : this.row.state ? state = this.row.state : state = '';

                if (state == 'undefined') {
                    state = '';
                }

                const request = axios.CancelToken.source();
                this.searchRequest = request;

                try {
                    const data = await axios.get(
                        `${this.data_api_url}&searchCityByName&uf=${state}&name=${name}`,
                        { cancelToken: request.token }
                    );

                    if (requestId === this.searchRequestId) {
                        this.cities = data.data || [];
                    }
                } catch (error) {
                    if (!axios.isCancel(error) && requestId === this.searchRequestId) {
                        this.cities = [];
                    }
                } finally {
                    if (this.searchRequest === request) {
                        this.searchRequest = null;
                    }
                }
            }, 250);
        },
        selected: function(city){
            if (this.searchTimer !== null) {
                clearTimeout(this.searchTimer);
                this.searchTimer = null;
            }

            if (this.searchRequest !== null) {
                this.searchRequest.cancel('Cidade selecionada.');
                this.searchRequest = null;
            }

            this.searchRequestId++;
            this.cities = [];
            this.row.city = city;
            this.$emit('selected', city, this.row);
        }
    }
})
