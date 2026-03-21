Vue.component('agcliente-form-input_text', {
    props: ['value', 'placeholder'],
    data: function(){
        return {
            innerValue: ''
        }
    },
    template: 
    `
        <input type="text" v-model="value" :placeholder='placeholder' class='form-control' />
    `,
    watch: {
        value: function(){
            this.$emit('change', this.value );
        }
    }
});