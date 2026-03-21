Vue.component('agdropdown', {
    props: ['position'],
    template: `
        <div class="dropdown">
            <span class="dropdown-toggle" data-toggle="dropdown">
                <slot name="text"></slot>
            </span>

            <div class="dropdown-menu" :class="'dropdown-menu-'+position">
                <slot name="actions"></slot>
            </div>

            <slot></slot>
        </div>
    `
});