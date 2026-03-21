Vue.component('agpanel', {
    template: `
        <div class="panel">
            <div v-if="$slots.heading" class="panel-heading">
                <slot name="heading"></slot>
            </div>

            <slot></slot>
        </div>
    `
});