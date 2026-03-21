Vue.component('agtable', {
    /**
     * pagination:
     * pageNumber - número da página atual
     * pageSize   - número de elementos a ser exibidos por página
     */
    props: [
        'columns-definition',
        'columns-data',
        'bulk-actions',
        'display-filter',
        'pagination',
        'front-office',
        'filter-type',
        'is-loading',
        'filters'
    ],
    data: function(){
        return {
            filterData: {},
            isLoadingData: false,
            paginationData: {
                pageNumber: 1,
                pageSize: 10,
                totalPages: 50
            }
        }
    },
    template: 
        `
        <div class="row">
          <div class="col-sm">
            <div class="row">
              <div class="col-sm">
                <div v-if="bulkActions != null && bulkActions.length" class="bulk-actions well">
                  <p>Ações em Massa</p>
                  <button class="btn btn-default" v-for="action in bulkActions" v-on:click="bulkActionClicked(action)">{{ action.label }}</button>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-sm">
                <div :class="tableContainerClass">
                  <table :class="tableClass" class="agtable" >
                      <agtable-header :isLoading="isLoadingData" :columns="columnsDefinition" :bulkActions="bulkActions"  :display-filter="displayFilter" :front-office="frontOffice" v-on:select-all="selectAll" v-on:unselect-all="unselectAll" v-on:search="search" @header-clicked="headerClicked"></agtable-header>
                      <agtable-body :isLoading="isLoadingData" :definition="columnsDefinition" :data="columnsDataPaginated" :bulkActions="bulkActions" v-on:toggle="toggle" :filter="displayFilter"></agtable-body>
                  </table>
                </div>
              </div>
            </div>

            <div v-if="this.paginationData" class="row pagination pagination-row mb-0">
              <div class="page-item" :class="previousDisabled">
                <a class="page-link" nohref="" @click="firstPage">1</a>
              </div>
              <div class="page-item previous" :class="previousDisabled">
                <a class="page-link" aria-label="Previous" @click="previousPage">
                  <i title="Próximo" class="icon icon-chevron-left"></i>
                </a>
              </div>
              <div class="page-item active">
                <input type="text" :value="this.paginationData.pageNumber" 
                ref="pageNumberInput"
                v-on:keyup.enter="setPageNumber"
                :size="this.paginationData.pageNumber.toString().length" 
                :psmax="Math.ceil(this.paginationData.totalPages / this.paginationData.pageSize)" 
                pslimit="this.paginationData.pageSize" aria-label="Selecione um número de página e pressione Enter">
              </div>
              <div class="page-item" :class="nextDisabled">
                <a class="page-link" aria-label="Next" @click="nextPage">
                  <i title="Próximo" class="icon icon-chevron-right"></i>
                </a>
              </div>
              <div class="page-item" :class="nextDisabled">
                <a class="page-link" @click="lastPage">{{ Math.ceil(this.paginationData.totalPages / this.paginationData.pageSize) }}</a>
              </div>
            </div>

          </div>
        </div>`,
    computed: {
        filteredData: function(){
            return this.applyFilterData(this.columnsData, this.filterData);
        },
        //retorna as colunas que devem ser exibidas para a página atual
        columnsDataPaginated: function(){
            let columnsData = this.columnsData;

            if (this.filterType !== 'remote' || 
                (this.paginationData.pageNumber > 1 && columnsData.length)) {
                columnsData = this.filteredData;
            }

            if(this.paginationData) {
                let begin;

                if(this.paginationData.pageNumber == 1) {
                    columnsData = this.quickSort(columnsData, 0, columnsData.length - 1);

                    if (this.filterType !== 'remote') {
                        this.paginationData.totalPages = columnsData.length;
                    }

                    begin = 0;
                } else {
                    begin = (this.paginationData.pageNumber * this.paginationData.pageSize) - this.paginationData.pageSize;
                }  

                if (columnsData.length > 0 && this.filterType !== 'remote') {
                    let end = begin + this.paginationData.pageSize;
                    let data = columnsData.slice(begin, end);
                    return this.quickSort(data, 0, data.length - 1);
                }
            }

          return this.quickSort(columnsData, 0, columnsData.length - 1);
        },
        previousDisabled: function(){
          if(!(this.paginationData.pageNumber > 1)) {
            return {
              "disabled": true
            }
          } else {
            return {
              "disabled": false
            }
          }
        },
        nextDisabled: function(){
          if(!(this.paginationData.pageNumber * this.paginationData.pageSize < this.paginationData.totalPages)) {
            return {
              "disabled": true
            }
          } else {
            return {
              "disabled": false
            }
          }
        },
        tableContainerClass: function(){
            return {
                "table-responsive" : true
            }
        },
        tableClass: function(){
            return {
                "table-responsive-row" : this.frontOffice,
                "table" : true,
                "table-striped" : this.frontOffice,
                "table-bordered" : this.frontOffice,
                "table-labeled" : this.frontOffice
            }
        }
    },
    mounted: function () {
        if(this.pagination) {
            this.paginationData.pageNumber = this.pagination.pageNumber;
            this.paginationData.pageSize = this.pagination.pageSize;
            this.paginationData.totalPages = this.pagination.totalPages;

            if (this.filterType == 'remote') {
                this.ajaxFilteredData();
            }
        } else {
            this.paginationData = null;
        }
    },
    methods: {
        /*************** Métodos referentes à aplicação de filtros *******************/
        ajaxFilteredData: async function() {
            this.isLoadingData = true;
            let filters = '';
            let pagination = '';

            filters = this.setFiltersString();

            if(this.paginationData) {
                pagination = `&page_size=${this.paginationData.pageSize}&page=${this.paginationData.pageNumber}`;
            }

            this.$emit("filterData", filters, pagination, () => { this.isLoadingData = false; });
        },
        setFiltersString: function() {
            let filters = '';
            let filterValue = 0;
            let filterDataKeys = Object.entries(this.filterData);

            if(filterDataKeys.length) {
                for (var i in this.columnsDefinition) {

                    filterDataKeys[i][1].value !== undefined ? filterValue = filterDataKeys[i][1].value :
                        filterDataKeys[i][1].value0 !== undefined ? filterValue = filterDataKeys[i][1].value0 : 0;

                    if (this.columnsDefinition[i].filterKey && filterValue) {
                        filters += `&filters${this.columnsDefinition[i].filterKey}=${filterValue}`;
                    }

                    filterValue = 0;
                }
            }

            return filters;
        },
        applyFilterData: function(columnsData, filter)
        {
            let that = this;
            let ret = [];

            $.each(columnsData, function(key, row) {
                if (that.rowMatchesFilters(row, filter)) {
                    ret.push(row);
                }
            });

            return ret;
        },
        rowMatchesFilters: function(row)
        {
            let matches = true;
            let that = this;

            $.each(row, function(name, value) {
                //extrai a coluna a partir do nome
                let column = that.getColumnByName(name);
                if (column === undefined) {
                    return;
                }

                //ignora se a coluna não for filtrável
                if (typeof column.filter !== 'undefined' && !column.filter) {
                    return;
                }

                //filtro não aplicado à coluna
                if (typeof that.filterData[name] === 'undefined') {
                    return;
                }

                // verifica se a coluna "casa" com o filtro aplicado
                if (!that.dataMatchesFilter(value, that.filterData[name])) {
                    matches = false;
                    return false;
                }
            });

            return matches;
        },
        dataMatchesFilter: function(value, filter)
        {
          if (value === null || value === undefined) {
            value = '';
          }
          
            if (typeof filter.type === 'undefined' || filter.type === 'value') {
                //filtro não aplicado a essa coluna
                if (typeof filter.value === 'undefined' || filter.value == '') {
                    return true;
                }

                return value.toString().toLowerCase().includes(filter.value.toLowerCase());
            }

            if (filter.type == 'interval') {
                let ret = true;

                if (filter.value0 !== '' && filter.value0 > value) {
                    ret = false;
                }

                if (filter.value1 !== '' && filter.value1 < value) {
                    ret = false;
                }

                return ret;
            }

            return true;
        },
        getColumnByName: function(name) {
            for (var i in this.columnsDefinition) {
                if (this.columnsDefinition[i].name == name) {
                    return this.columnsDefinition[i];
                }
            }
        },

        // Implementação dos métodos para ordenação eficiente utilizando Quick Sort
        swap(items, leftIndex, rightIndex){
          var temp = items[leftIndex];
          items[leftIndex] = items[rightIndex];
          items[rightIndex] = temp;
        },
        partition(items, left, right) {
          var pivot   = items[Math.floor((right + left) / 2)].id, //Elemente do meio
              i       = left, //Indicador da esquerda
              j       = right; //Indicador da direita
          while (i <= j) {
              while (items[i].id < pivot) {
                  i++;
              }
              while (items[j].id > pivot) {
                  j--;
              }
              if (i <= j) {
                this.swap(items, i, j); //Trocar 2 elementos de lugar
                  i++;
                  j--;
              }
          }
          return i;
        },
        quickSort(items, left, right) {
          return items;
          
          var index;
          if (items.length > 1) {
              index = this.partition(items, left, right); //Resultado da partição
              if (left < index - 1) { //Elementos para o lado esquerdo
                this.quickSort(items, left, index - 1);
              }
              if (index < right) { //Elementos para o lado direito
                this.quickSort(items, index, right);
              }
          }
          return items;
        },
        // Fim da implementação dos métodos de ordenação

        selectAll: function(){
            this.$emit("select-all");
        },
        unselectAll: function(){
            this.$emit("unselect-all");
        },
        toggle: function(obj){
            this.$emit("toggle", obj);
        },
        bulkActionClicked: function(ba){
            this.$emit("bulk-action-clicked", ba);
        },
        search: async function(filterData){
            this.filterData = filterData;

            if (this.filterType == 'remote') {
                this.ajaxFilteredData();
            } else {
                this.$emit("search");
            }
        },
        headerClicked: function(column) {
            this.$emit('header-clicked', column);
        },
        setPageNumber: function() {
          let pageNumberInput = this.$refs.pageNumberInput.value;
          if(pageNumberInput <= Math.ceil(this.paginationData.totalPages / this.paginationData.pageSize)
          && pageNumberInput > 0) {
            this.paginationData.pageNumber =  parseInt(pageNumberInput);
          } else {
            this.$refs.pageNumberInput.value = this.paginationData.pageNumber.toString();
          }
        },
        firstPage : function(){
            this.paginationData.pageNumber = 1;
            this.checkColumnsDataLength();
            return false;
        },
        previousPage : function(){
            this.paginationData.pageNumber--;
            this.checkColumnsDataLength();
            return false;
        },
        nextPage : function(){
            this.paginationData.pageNumber++;
            this.checkColumnsDataLength();
            return false;
        },
        lastPage : function(){
            this.paginationData.pageNumber = Math.ceil(this.paginationData.totalPages / this.paginationData.pageSize);
            this.checkColumnsDataLength();
            return false;
        },
        checkColumnsDataLength: function() {
            if(this.columnsData.length && this.filterType == 'remote') {
                this.ajaxFilteredData();
            }
        }
    },
    watch: {
        isLoading: function() {
            this.isLoadingData = this.isLoading;
        },
        "paginationData.pageNumber": function() {
            this.$emit('pageChange', this.paginationData);
        },
        pagination: function() {
            this.paginationData.pageNumber = this.pagination.pageNumber;
            this.paginationData.pageSize = this.pagination.pageSize;
            this.paginationData.totalPages = this.pagination.totalPages;
        }
    }
});
