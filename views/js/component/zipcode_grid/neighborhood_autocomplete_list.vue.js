Vue.component('agti-zipcode-grid-neighborhood-autocomplete', {
    props: ['objects'],
    data: function(){
        return {
            neighborhood: '',
            isCleaning: false
        }
    },
    template:
    `
        <div>
            <span class="btn-limpar" @click="reset()">
                Limpar
            </span>
            <div>
                <div v-for="neighborhood in objects" @click="setNeighborhood(neighborhood)">{{ neighborhood.neighborhood }} ( {{neighborhood.state}} )</div>
            </div>
        </div>
    `,
    watch: {
        neighborhood: function(){
            this.$emit('selected', this.neighborhood, this.isCleaning);
            this.isCleaning = false
        }
    },
    methods: {
        reset: function(){
            this.isCleaning = true;

            this.setNeighborhood({
                city: this.neighborhood.city,
                neighborhood: '',
                state: this.neighborhood.state,
            });
        },
        setNeighborhood: function(neighborhood)
        {
            this.neighborhood = neighborhood;
        }
    }
})
