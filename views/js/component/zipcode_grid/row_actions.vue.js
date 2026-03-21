Vue.component('agti-zipcode-grid-row-actions', {
    props: ['idx'],
    template: 
    `
        <div>
            <i class="material-icons row-action-icon" @click="remove">delete</i>
        </div>
    `,
    methods: {
        remove: function(){
            if (window.confirm("Deseja realmente excluir esta linha?")) {
                this.$emit('remove', this.idx);
            }
        }
    }
});
