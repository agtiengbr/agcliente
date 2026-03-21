window.addEventListener('load', function(){
    Vue.component('agprogress-bar', {
        props: ['percent', 'height', 'bgcolor', 'textcolor', 'label'],
        data: function(){
            return {
                timeStarted: ''
            }
        },
        template: `
            <div class="agprogress-bar">
                <label>{{ customLabel }}</label>
                <div class="bar" :style=myStyle>{{ percent }}%</div>
            </div>
        `,
        computed: {
            myStyle: function(){
                return {
                    width: this.percent + "%",
                    height: this.getHeight + "px",
                    backgroundColor: this.getBgColor,
                    color: this.getTextColor,
                    textAlign: 'center'
                }
            },
            getHeight: function(){
                if (this.height != null) {
                    return this.height;
                }

                return 20;
            },
            getBgColor: function(){
                if (this.bgcolor != null) {
                    return this.bgcolor;
                }

                return 'gray';
            },
            getTextColor: function(){
                if (this.textcolor != null) {
                    return this.textcolor;
                }

                return 'white';
            },
            timeStartedCalc: function(){
                if (this.percent > 0 && this.timeStarted == '') {
                    this.timeStarted = new Date().getTime();
                }

                return this.timeStarted;
            },
            remainingTime: function(){
                if (this.timeStarted == '') {
                    return;
                }

                let now = new Date().getTime();
                let ellapsed = (now - this.timeStarted) / 1000;
                let expected = ellapsed / (this.percent/100) - ellapsed;

                if (expected == 0) {
                    return;
                }

                let remaining_time;
                if (expected > 60) {
                    remaining_time = (expected / 60).toFixed(2) + " minutos restantes.";
                } else {
                    remaining_time = (expected / 60).toFixed(2) + " segudos restantes.";
                }

                return remaining_time;
            },
            customLabel: function(){
                let ret = '';
                let remainingTime = this.remainingTime;

                if (this.label != null) {
                    ret += this.label;
                }

                if (remainingTime != null) {
                    ret += `(${remainingTime})`;
                }

                return ret;
            },
        },
        watch: {
            percent: function(){
                if (this.percent > 0 && this.timeStarted == '') {
                    this.timeStarted = new Date().getTime();
                }
            }
        }
    });
})