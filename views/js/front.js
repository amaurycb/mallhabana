let MallHabanaFront = function() {

    return {
        init: function() {
            setTimeout(function() {
                $('body').find('aside#notifications .container article.alert').hide(300);
            }, 5000);

            if ($("#mallhabana_can_delivery").val() == "0") {
                $('div.delivery-options-list').html('<p class="alert alert-danger">Desafortunadamente, no disponemos de ningún transportista disponible para su dirección de envío.</p>')
            }
        },
    };

}();

jQuery(document).ready(function() {
    MallHabanaFront.init();
});