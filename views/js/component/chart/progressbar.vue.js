Vue.component("agcliente-progress-bar", {
    props: ["percent", "color1", "color2", "color3"],
    computed: {
        internalColor: function(){
            if (this.percent < 33) {
                return this.color1;
            }

            if (this.percent < 80) {
                return this.color2;
            }


            return this.color3;
        },
        internalClass: function(){
            if (this.percent < 33) {
                return 'empty';
            }

            if (this.percent < 80) {
                return 'middle';
            }


            return 'full';
        }
    },
    template:
    `
        <div class="progress-bar">
            <div><slot name="default"></slot></div>

            <div class="bar" style="height: 20px">
                <div class="filled" :style="{width: percent + '%', backgroundColor: internalColor, height: '100%', maxWidth: '100%'}" :class="internalClass">{{ percent.toFixed(2) + '%' }}</div>
            </div>
        </div>
    `
});