document.addEventListener('DOMContentLoaded', function(){
    async function doNotReviewAjax(module_name)
    {
        return new Promise((resolve, reject) => {
            $.ajax({
                dataType : 'json',
                method: 'POST',
                data: {
                    agcliente_do_not_review: module_name
                }
            }).then(function(){
                resolve();
            }).fail(function(){
                reject();
            });
        });
    }

    async function doNotReview(module_name)
    {
        try {
            await doNotReviewAjax(module_name);
            $('[data-module="' + module_name + '"]').closest('li').remove();
            $.growl.notice({title: '', message: 'Você não verá mais solicitações de reviews desse módulo.'});
        } catch (e) {
            $.growl.error({title: '', message: 'Ocorreu um erro inesperado.'});
        }
    }

    $('.do-not-review').click(function(){
        doNotReview($(this).attr('data-module'));
        return false;
    });
});