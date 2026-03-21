// register modal component
window.addEventListener('load', function(){
    Vue.component("aginput-text", {
        props: {
            classname: {
                default: ''
            },
            placeholder: {
                default: ''
            },
            required: {
                default: false
            },
            hasError: false
        },
        data: function(){
            return {
                value: ''
            };
        },
        methods: {
            validate: function(){
                console.log(this.value);
            }
        },
        template: `
            <input
                type="text"
                v-bind:class="{'has-error': hasError }"
                class="form-control aginput aginput-text"
                v-model="value"
                :placeholder="placedholder"
                @change="validate"
                :required="{ required }"

            />
        `
    });
});