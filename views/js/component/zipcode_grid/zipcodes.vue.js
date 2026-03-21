Vue.component('agti-zipcodes-grid-zipcodes', {
    props: {
        min: {
            default: function(){
                return '';
            }
        },
        max: {
            default: function(){
                return '';
            }
        }
    },
    data: function(){
        return {
            minData : '',
            maxData : ''
        }
    },
    template: 
    `
    <div>
        <div class="row">
            <div class="col-6 col-sm-6 col-md-6 col-lg-6">
                De <input @input="resetOnInput()" class="border-bottom form-control" type="text" v-model="minData" v-maska="'#####-###'"/>
            </div>

            <div class="col-6 col-sm-6 col-md-6 col-lg-6">
                A <input @input="resetOnInput()" class="border-bottom form-control" type="text" v-model="maxData" v-maska="'#####-###'"/>
            </div>
        </div>
        <span class="custom-hidden">
            hidden
        </span>
    </div>
    `,
    created: function(){
        this.formatZipcode();
    },
    watch: {
        min: function() {
            this.formatZipcode();
            this.$emit('range', {min: this.minData, max: this.maxData});
        },
    },
    methods: {
        range: function(range){
            this.$emit('range', range[this.minData, this.maxData]);
        },
        resetOnInput: function(){
            this.formatZipcodeData();
            this.$emit('range', {min: this.minData, max: this.maxData});
            this.$emit('resetAll');
        },
        formatZipcode: function() {
            this.minData = this.min.toString().replace(/\D/g, '')
            .replace(/(\d{5})(\d)/, '$1-$2')
            .replace(/(-\d{3})\d+?$/, '$1');

            this.maxData = this.max.toString().replace(/\D/g, '')
            .replace(/(\d{5})(\d)/, '$1-$2')
            .replace(/(-\d{3})\d+?$/, '$1');
        },
        formatZipcodeData: function() {
            this.minData = this.minData.toString().replace(/\D/g, '')
            .replace(/(\d{5})(\d)/, '$1-$2')
            .replace(/(-\d{3})\d+?$/, '$1');

            this.maxData = this.maxData.toString().replace(/\D/g, '')
            .replace(/(\d{5})(\d)/, '$1-$2')
            .replace(/(-\d{3})\d+?$/, '$1');
        }
    }
})
