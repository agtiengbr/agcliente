// register modal component
Vue.component("agmodal", {
    props: {
        classname: {
            default: ''
        }
    },
    template: `
    <transition name="modal">
        <div class="agmodal on" v-bind:class="classname" @click="modalClicked">
            <slot name="default"></slot>
            <div class="agmodal-content">
                <div class="modal-header"><slot name="header"></slot></div>
                <div class="modal-body">
                    <slot name="body"></slot>
                </div>
                <div class="modal-footer"><slot name="footer"></slot></div>
            </div>
        </div>
    </transition>
    `,
    methods: {
        modalClicked: function(e){
            if (e.target.classList.contains('agmodal')) {
                this.$emit('backdropClicked');
                this.$emit('backdrop-clicked');
            }
        }
    }
});