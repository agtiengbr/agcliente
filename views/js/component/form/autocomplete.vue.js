window.addEventListener('load', function(){
    Vue.component('agcliente-form-autocomplete', {
        props: ['component','objects', 'listeners'],
        data: function(){
            return {
                value: '',
                timer: ''
            }
        },
        template:
        `
            <div>
                <agcliente-form-input_text @change="change" :value='value'></agcliente-form-input_text>
                <div class='choose'>
                    <component :is='component' :objects="objects" @selected="selected" v-on="listeners"></component>
                </div>
            </div>
        `,
        methods: {
            change: function(value)
            {
                this.value = value;
                let that = this;

                if (this.timer !== '') {
                    clearTimeout(this.timer);
                }

                this.timer = setTimeout(function(){
                    that.$emit('change', value);
                }, 500);
            },
            selected: function(){
                this.value = '';
            }
        }
    });
})