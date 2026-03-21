window.addEventListener('load', function(){
    Vue.component('agcliente-notification-alert', {
        props: {
            errors: {
                type: Array,
                default() {
                    return [];
                }
            },
            warnings: {
                type: Array,
                default() {
                    return [];
                }
            },
            infos: {
                type: Array,
                default() {
                    return [];
                }
            },
            successes: {
                type: Array,
                default() {
                    return [];
                }
            }
            
        },
        computed: {
            display: function(){
                return this.successes.length > 0 || this.warnings.length > 0 || this.errors.length > 0 || this.infos.length > 0
            }
        },
        template:
        `
            <div class="notification-alerts" v-if="display">
                <div class="alert alert-success" v-if="successes.length > 0">
                    <ul>
                        <li v-for="message in successes">{{ message }}</li>
                    </ul>
                </div>

                <div class="alert alert-info" v-if="infos.length > 0">
                    <ul>
                        <li v-for="message in infos">{{ message }} </li>
                    </ul>
                </div>

                <div class="alert alert-warning" v-if="warnings.length > 0">
                    <ul>
                        <li v-for="message in warnings">{{ message }} </li>
                    </ul>
                </div>

                <div class="alert alert-danger" v-if="errors.length > 0">
                    <ul>
                        <li v-for="message in errors">{{ message }} </li>
                    </ul>
                </div>
            </div>
        `
    })
});