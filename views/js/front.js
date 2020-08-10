let MallHabanaFront = function() {

    return {
        init: function() {
            setTimeout(function() {
                $('body').find('aside#notifications .container article.alert').hide(300);
            }, 5000);

            //$('section#checkout-addresses-step form').ready(function() { $('select[name="id_state"]').select2() })
        },
    };

}();

jQuery(document).ready(function() {
    MallHabanaFront.init();
});