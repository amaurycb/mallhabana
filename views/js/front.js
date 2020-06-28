let MallHabanaFront = function() {

    return {
        init: function() {
            setTimeout(function() {
                $('body').find('aside#notifications .container article.alert').hide(300);
            }, 5000);
        },
    };

}();

jQuery(document).ready(function() {
    MallHabanaFront.init();
});