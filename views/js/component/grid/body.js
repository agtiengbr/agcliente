Vue.component('agtable-body', {
    props: ['filter', 'definition', 'data', 'bulk-actions', 'isLoading'],
    template: 
        `<tbody>
            <agcliente-loading v-if="isLoading" :size="50"></agcliente-loading>
            <tr v-for="row in data">
                <td v-if="bulkActions != null && bulkActions.length">
                    <input type="checkbox" v-model="row.checked" v-on:click="toggle(row.obj)"/>
                </td>

                <td v-for="column in definition" :class="column.class">
                    <template v-if="typeof row[column.name] !== 'undefined'">
                        <span v-if="columnType(row[column.name], column) === 'text'">
                            {{ columnValue(row[column.name]) }}
                        </span>
                        <component v-if="columnType(row[column.name], column) === 'component'" :is="row[column.name].component" v-bind="row[column.name].props" v-on="row[column.name].listeners"></component>
                        <span v-else-if="columnType(row[column.name], column) === 'html'" v-html="row[column.name].value"></span>

                    </template>
                    <template v-else>
                        -
                    </template>
                </td>
                <td v-if="filter"></td>
            </tr>
        </tbody>`,
    methods: {
        columnType: function(row, column){
            if (row === null) {
                return 'text';
            }

            if (typeof row.type !== 'undefined') {
                return row.type;
            }

            if (typeof column.type !== 'undefined') {
                return column.type;
            }

            return 'text';
        },
        toggle: function(obj){
            this.$emit("toggle", obj);
        },
        columnValue: function(column) {
            if (column === null) {
                return '-';
            }
            
            if ( typeof column !== 'object') {
                return column;
            }

            return column.value;
        }
    }
        
});
