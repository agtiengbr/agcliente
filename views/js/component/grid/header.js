Vue.component('agtable-header', {
    props: ['columns', 'bulk-actions', 'display-filter', 'actions', "front-office", "filters", "isLoading"],
    data: function(){
        let filterData = this.setFilterData();

        return {
            selectedAll: 0,
            filterData: filterData
        }
    },
    template: 
        `<thead :class="theadClass">
            <tr class="">
                <td v-if="bulkActions != null && bulkActions.length">
                    <th></th>
                </td>
                
                <th v-for="column in columns" @click="headerClicked(column)" :class="column.class">
                    <span class="title_box fixed-width-md">{{ column.title }} </span>
                </th>
                <th v-if="actions && actions.length > 0"></th>
                <th v-if="displayFilter" class="fixed-width-md"></th>
            </tr>
            <tr class="filter" v-if="typeof displayFilter === 'undefined' || displayFilter">
                <th v-if="bulkActions != null && bulkActions.length">
                    <input type="checkbox" v-on:click="selectAll"/>
                </th>
                <th v-for="column in columns">
                    <input @keyup.enter="search" class="form-control" v-if="filterType(column) == 'text'" v-model="filterData[column.name].value"/>
                    
                    <div v-if="filterType(column) == 'number'">
                        <input @keyup.enter="search" class="form-control" v-model="filterData[column.name].value0"/>
                        <input @keyup.enter="search" class="form-control mt-1" v-model="filterData[column.name].value1"/>
                    </div>

                    <select class="form-control" v-if="filterType(column) == 'select'">
                        <option value="">{{ i18n.allResults }}</option>
                        <option v-for="option in filterOptions(column.filter)" :value="option.value">{{ option.text }}</option>
                    </select>
                </th>
                <th v-if="displayFilter" class="fixed-width-md">
                    <div>
                        <button class="btn btn-primary" @click="search" :disabled="isLoading">{{ i18n.filter }}</button>
                    </div>
                    <div>
                        <button class="btn btn-default mt-1" @click="clean" :disabled="isLoading">{{ i18n.clear }}</button>
                    </div>
                </th>
            </tr>
        </thead>`,
    computed: {
        theadClass: function(){
            return {
                "thead-default" : this.frontOffice
            };
        },
        i18n: function() {
            const d = (typeof window !== 'undefined' && window.agcliente_i18n) ? window.agcliente_i18n : {};
            return Object.assign({
                filter: 'Filtrar',
                clear: 'Limpar',
                allResults: 'Todos os resultados',
                yes: 'Sim',
                no: 'Não'
            }, d);
        }
    },
    methods: {
        setFilterData: function() {
            let filterData = {};

            for (var i in this.columns) {
                if (this.columns[i].dataType === 'number') {
                    filterData[this.columns[i].name] = {type : 'interval'};
                } else {
                    filterData[this.columns[i].name] = {type : 'value'};
                }
            }

            return filterData;
        },
        selectAll: function(){
            if (!this.selectedAll) {
                this.selectedAll = 1;
                this.$emit("select-all");
            } else {
                this.selectedAll = 0;
                this.$emit("unselect-all");
            }
        },
        search: function(){
            this.$emit("search", this.filterData);
        },
        filterType: function(column)
        {
            if (typeof column.filter !== 'undefined' && !column.filter) {
                return null;
            }

            if (typeof column.dataType === 'undefined') {
                return 'text';
            }

            if (column.dataType === 'number') {
                return 'number';
            }

            // if (filter.type == 'bool') {
            //     return 'select';
            // }

            // return filter.type;
        },
        filterOptions: function(filter)
        {
            if (filter.type == 'bool') {
                return [
                    {
                        text: this.i18n.yes,
                        value: 1
                    },
                    {
                        text: this.i18n.no,
                        value: 0
                    }
                ];
            }

            return filter.options;
        },
        headerClicked: function(column)
        {
            this.$emit('header-clicked', column);
        },
        clean: function() {
            this.filterData = this.setFilterData();
            this.$emit("search", this.filterData);
        }
    }
});
