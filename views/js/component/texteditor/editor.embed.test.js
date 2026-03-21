class EmbedIframe
{
    static get toolbox() {
        console.log('chamou');
        return {
            title: 'Embed (iframe)',
            icon: '<p>teste</p>'
        };
    }

    render() {
        let iframe = document.createElement('iframe');
        iframe.src = location.href;
        iframe.width = '100%';

        return iframe;
    }

    save() {
        return {

        };
    }
}