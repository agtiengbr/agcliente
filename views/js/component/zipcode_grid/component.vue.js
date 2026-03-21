Vue.component('agti-zipcodes-grid', {
    props: ['rows', 'api-url'],
    data: function(){
        return {
            data_api_url: '',
            rows_data: [],
            treated_data: [],
            table_data: [],
            isLoading: false,
            columns: [
                {
                    title: 'Região',
                    name: 'zone'
                },
                {
                    title: 'Estado',
                    name: 'state'
                },
                {
                    title: 'Cidade',
                    name: 'city'
                },
                {
                    title: 'Bairro',
                    name: 'neighborhood'
                },
                {
                    title: 'CEPs',
                    name: 'zipcodes'
                },
                {
                    title: 'Ações',
                    name: 'actions'
                }
            ],
        }
    },
    template: 
    `
        <div>
            <agtable
                :columnsDefinition="columns"
                :columnsData="table_data"
                :displayFilter="false"
                :isLoading="isLoading"
            >
            </agtable>
            <div class="text-center">
                <div id="add-row-btn" class="btn btn-block btn-default" @click="addRow">
                    <i class="material-icons">add</i>
                    Adicionar nova linha
                </div>
            </div>
        </div>
    `,
    mounted: function() {
        this.apiUrl ? this.data_api_url = this.apiUrl : this.data_api_url = location.href;
        this.setRows();
    },
    watch: {
        rows: function(){
            this.setRows();
        },
        rows_data: function() {
            this.updateTableData();
        }
    },
    methods: {
        setRows: function() {
            if (typeof this.rows !== 'undefined') {
                this.rows_data = this.rows;
            }
        },
        addRow: function(row){
            if(row.id || (row.cep_end && row.cep_start)) {
                this.rows_data.push(row);
            } else {
                this.rows_data.push({});
            }
        },
        updateTableData: function(){
            this.table_data = [];
            let that = this;

            this.treatData();

            $.each(this.rows_data, function(key, row){
                row.city = that.flattenJSON(row.city);
                row.neighborhood = that.flattenJSON(row.neighborhood);
                that.table_data.push({
                    zipcodes: {
                        type: 'component',
                        component: 'agti-zipcodes-grid-zipcodes',
                        props: {
                            min: row.min ? row.min : row.cep_start,
                            max: row.max ? row.max : row.cep_end,
                        },
                        listeners: {
                            range: function(range) {
                                row.cep_start = range.min;
                                row.cep_end = range.max;
                                row.min = range.min;
                                row.max = range.max;
                                that.setRange(range);
                                that.toggleLoading();
                            },
                            resetAll: function() {
                                row.zone = '0';
                                row.uf = '0';
                                row.city = '0';
                                row.neighborhood = '0';

                                that.updateTableData();
                            }
                        }
                    },
                    zone: {
                        type: 'component',
                        component: 'agti-zipcodes-grid-zones',
                        props: {
                            zone: row.zone ? row.zone : row.region,
                            row: row
                        },
                        listeners: {
                            changed: function(zone, row) {
                                that.toggleLoading();
                                row.zone = zone;
                                that.fillZone(zone, row).then(() => {
                                    that.updateTableData();
                                });
                            }
                        }
                    },
                    state: {
                        type: 'component',
                        component: 'agti-zipcode-grid-states',
                        props: {
                            row: row,
                            uf: row.uf ? row.uf : row.state,
                        },
                        listeners: {
                            changed: function(uf, row) {
                                that.toggleLoading();
                                row.uf = uf;
                                that.fillUf(uf, row).then(() => {
                                    that.updateTableData();
                                });
                            }
                        }
                    },
                    city: {
                        type: 'component',
                        component: 'agti-zipcode-grid-cities',
                        props: {
                            row: row,
                            city: row.city,
                            apiUrl: that.data_api_url
                        },
                        listeners: {
                            selected: function(city, row) {
                                that.toggleLoading();
                                if(city.city == '') {
                                    row.neighborhood = ''
                                }
                                row.city = city;
                                row.uf = city.state;
                                that.fillCity(city, row, false).then(() => {
                                    that.updateTableData();
                                });
                            }
                        }
                    },
                    neighborhood: {
                        type: 'component',
                        component: 'agti-zipcode-grid-neighborhoods',
                        props: {
                            row: row,
                            neighborhood: row.neighborhood,
                            city: row.city,
                            state: row.uf ? row.uf : row.state,
                            apiUrl: that.data_api_url
                        },
                        listeners: {
                            selected: function(neighborhood, row, isCleaning) {
                                that.toggleLoading();
                                row.neighborhood = neighborhood;
                                that.fillNeighborhood(neighborhood, row, isCleaning).then(() => {
                                    that.updateTableData();
                                });
                            }
                        }
                    },
                    actions: {
                        type: 'component',
                        component: 'agti-zipcode-grid-row-actions',
                        props: {
                            idx: key
                        },
                        listeners: {
                            remove: that.deleteRow
                        }
                    }
                });
            });
            
            this.setRange();
        },
        flattenJSON: function(obj = {}, res = {}, extraKey = '') {
            for(key in obj){
                if(typeof obj[key] !== 'object'){
                   res[key] = obj[key];
                }else{
                   this.flattenJSON(obj[key], res, `${key}.`);
                };
            };
            return res;
        },
        toggleLoading: function() {
            this.isLoading ? this.isLoading = false : this.isLoading = true;
            setTimeout(() => this.isLoading = false, 3000);
        },
        treatData: function() {
            let min, max, neighborhood;
            this.rows_data.forEach((row, index) => {
                min = row.min ? row.min : row.cep_start ? row.cep_start : row.zipcode_begin;
                max = row.max ? row.max : row.cep_end ? row.cep_end : row.zipcode_end;
                if(row.neighborhood) {
                    neighborhood = row.neighborhood.neighborhood ? row.neighborhood.neighborhood : '';
                }
                state = row.uf ? row.uf : row.state;
                if(min && max) {
                    this.rows_data[index].min = min.toString().replace(/\D/g,'').padStart(8, "0");
                    this.rows_data[index].max = max.toString().replace(/\D/g,'').padStart(8, "0");
                    this.rows_data[index].neighborhood.neighborhood = neighborhood;
                    this.rows_data[index].state = state;
                }
            });
        },
        setRange: function() {
            this.treatData();
            this.$emit('range', this.rows_data);
        },
        fillZone: async function(zone, row) {
            if(zone == "brasil") {
                row.min = "00000-000";
                row.max = "99999-999";
                row.uf = '0';
                row.city = '0';
                row.neighborhood = '0';
            } else {
                let data = await axios.get(`${this.data_api_url}&searchByZone&zone=${zone}`);

                if (data.data[0] && data.data[0].min && typeof data.data[0].min !== 'undefined') {
                    row.min = data.data[0].min.toString();
                    row.max = data.data[0].max.toString();
                    row.cep_start = data.data[0].min.toString();
                    row.cep_end = data.data[0].max.toString();
                    row.uf = '0';
                    row.city = '0';
                    row.neighborhood = '0';
                }
            }

            this.isLoading = false;
        },
        fillUf: async function(uf, row) {
            if(uf == 0 || uf == 'undefined') {
                if(row.city.city !== '' && row.city.city !== '0'
                    && row.city !== '' && row.city !== '0') {
                    await this.fillCity(row.city, row, false);
                } else {
                    row.min = ''
                    row.max = ''
                    row.zone = '0';
                }
            } else {
                if(!uf || uf == 'undefined') {
                    uf = '';
                }

                let data = await axios.get(`${this.data_api_url}&searchByUf&uf=${uf}`);
                
                if (typeof data.data[0].min !== 'undefined') {
                    row.min = data.data[0].min.toString();
                    row.max = data.data[0].max.toString();
                    row.cep_start = data.data[0].min.toString();
                    row.cep_end = data.data[0].max.toString();
                    row.zone = '0';
                    row.city = '0';
                    row.neighborhood = '0';
                }
            }

            this.isLoading = false;
        },
        fillCity: async function(city, row, isCleaning){
            if(city.city == '') {
                await this.fillUf(row.state !== 'undefined' ? row.state : row.uf, row);
            } else {
                let data = await axios.get(`${this.data_api_url}&searchByCityAndUf&uf=${row.uf ? row.uf : city.state}&city=${city.city}`);

                if (typeof data.data.length !== 'undefined' && data.data.length) {

                    if(!isCleaning && data.data.length > 1) {
                        this.rows_data.pop();
                        data.data.forEach((zipcode) => {
                            this.addRow({
                                cep_end: zipcode.zipcode_end,
                                cep_start: zipcode.zipcode_begin,
                                min: zipcode.zipcode_begin,
                                max: zipcode.zipcode_end,
                                neighborhood: row.neighborhood,
                                region: row.region,
                                state: row.state,
                                uf: row.uf,
                                zone: row.zone,
                                city: row.city
                            });
                        });
                    } else {
                        if (typeof data.data[0].zipcode_begin !== 'undefined') {
                            row.min = data.data[0].zipcode_begin.toString();
                            row.max = data.data[0].zipcode_end.toString();
                            row.zone = '0';
                        }
                    }
                }
            }

            this.isLoading = false;
        },
        fillNeighborhood: async function(neighborhood, row, isCleaning){
            if(neighborhood.neighborhood == '') {
                await this.fillCity({
                    city: row.city.city,
                    state: row.state
                }, row, isCleaning);
            } else {
                let data = await axios.get(`${this.data_api_url}&searchByNeighborhood&uf=${neighborhood.state}&city=${neighborhood.city}&neighborhood=${neighborhood.neighborhood}`);

                if (typeof data.data !== 'undefined') {
                    if (typeof data.data.min !== 'undefined') {
                        row.min = data.data.min.toString();
                        row.max = data.data.max.toString();
                        row.cep_start = data.data.min.toString();
                        row.cep_end = data.data.max.toString();
                        row.zone = '0';
                        row.neighborhood = neighborhood;
                    }
                }
            }

            this.isLoading = false;
        },
        deleteRow: function(idx)
        {
            this.rows_data.splice(idx, 1);
            this.setRange();
        }
    }
});
