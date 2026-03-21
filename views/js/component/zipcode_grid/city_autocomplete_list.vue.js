Vue.component('agti-zipcode-grid-city-autocomplete', {
    props: ['objects'],
    data: function(){
        return {
            city: ''
        }
    },
    template:
    `
        <div>
            <span class="btn-limpar" @click="reset()">
                Limpar
            </span>
            <div>
                <div v-for="city in objects" @click="setCity(city)">{{ city.city }} ( {{city.state}} )</div>
            </div>
        </div>
    `,
    watch: {
        city: function(){  
            this.$emit('selected', this.city);
        }
    },
    methods: {
        reset: function(){
            this.setCity({city: '', state: this.city.state});
        },
        setCity: function(city)
        {
            this.city = city;
        }
    }
})
