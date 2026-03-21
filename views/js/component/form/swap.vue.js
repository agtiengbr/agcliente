Vue.component('agcliente-form-swap', {
    props: {
        alldata: {
            type: Array,
            default: function(){
                return [];
            },
        },
        selecteddata: {
            type: Array,
            default: function(){
                return [];
            }
        },
        addtext: {
            type: String,
            default: function(){
                return 'Adicionar'
            }
        },
        removetext: {
            type: String,
            default: function(){
                return 'Cancelar'
            }
        }
    },
    data: function(){
        return {
            internalSelecteddata: this.selecteddata,
            optionsToAdd: [],
            optionsToRemove: []
        };
    },
    watch: {
        internalSelecteddata: function(){
            this.$emit('changed', this.internalSelecteddata);
        }
    },
    computed: {
        optionsForFirstSelect: function(){
            let ret = [];
            let that = this;

            for (let i in that.alldata) {
                if (that.internalSelecteddata.find(el => el == that.alldata[i].id) === undefined) {
                    ret.push(that.alldata[i]);
                }
            }

            return ret;
        },
        optionsForSecondSelect: function(){
            let ret = [];
            let that = this;

            for (let i in that.alldata) {
                if (that.internalSelecteddata.find(el => el == that.alldata[i].id) !== undefined) {
                    ret.push(that.alldata[i]);
                }
            }
            return ret;
        }
    },
    template: `
        <div class='row'>
            <div class='col-md-6'>
                <select multiple v-model="optionsToAdd">
                    <option v-for="data in this.optionsForFirstSelect" :value="data.id">{{ data.text }}</option>
                </select>
                <button class="btn btn-default btn-block" @click="add">{{ this.addtext }}</button>
            </div>

            <div class='col-md-6'>
                <select multiple v-model="optionsToRemove">
                    <option v-for="data in this.optionsForSecondSelect" :value="data.id">{{ data.text }}</option>
                </select>
                <button class="btn btn-default btn-block" @click="remove">{{ this.removetext }}</button>
            </div>
        </div>
    `,
    methods: {
        add: function(){
            let that = this;

            $.each(this.optionsToAdd, function(key, value){
                let status = that.getById(value);
                that.internalSelecteddata.push(status.id);
            });
            
            this.internalSelecteddata = [...new Set(that.internalSelecteddata)];
            this.optionsToAdd = [];
        },
        remove: function(){
            let that = this;

            this.internalSelecteddata = this.internalSelecteddata.filter(function(el){
                //verifica se "el" está nos elementos a serem removidos
                let is = that.optionsToRemove.find(function(el2){
                    return el2 == el;
                });

                return is === undefined;
            });
        },
        getById: function(id){
            return this.alldata.find(function(status){
                return status.id == id;
            });
        },
    }
    
})