Vue.component('agcliente-tabs', {
    props: {
        tabs: {
            type: Array,
            default: function(){
                return [];
            }
        },
        tab_type: {
            type: String,
            default: function(){
                return 'horizontal';
            }
        }
    },
    template:
    `
        <div class="agcliente-tabs" :class="{vertical: tab_type=='vertical', horizontal: tab_type=='horizontal'}">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item" :class="{active: idx==0}"  v-for="(tab, idx) in tabs">
                    <a data-toggle="tab" :href="'#' + getTabId(tab, idx)"><i :class="tab.icon"></i> {{ tab.text }}</a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane" :class="{active: idx==0}" :id="getTabId(tab, idx)" v-for="(tab, idx) in tabs">
                    <template v-if="tab.content.text">
                        {{ tab.content.text }}
                    </template>

                    <component v-if="tab.content.component" :is="tab.content.component"  v-bind="tab.content.props" :on="tab.content.listeners"></component>
                </div>
            </div>
        </div>
    `,
    methods: {
        getTabId: function(tab, idx)
        {
            if (tab.id) {
                return tab.id;
            }

            return `tab${idx}`;

        }
    }
})