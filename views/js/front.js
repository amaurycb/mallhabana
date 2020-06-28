let MallHabanaFront = function() {
    let alertBox = $('.alert_notice').first().clone();
    let alertDefaultHTML = alertBox.html("");

    //Make a copy of alert container for this module notifications
    $('.alert_notice').first().parent().append(alertBox);

    /**
     * Set cookie  
     * @param string name 
     * @param string cvalue 
     * @param int exdays 
     */
    function setCookie(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }


    /**
     * Read cookie  
     * @param string name 
     */
    let getCookie = function(cname) {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    /**
     * Show templante alert in a wrong case  
     * @param string message 
     * @param string type {'info', 'danger', 'warning'}
     */
    let showAlertMessage = function(message, type) {
        let content = `<p class="alert_no_item alert alert-${type}">${message}</p>`;
        alertBox.html(content).addClass('active');

        setTimeout(function() {
            alertBox.removeClass('active').html(alertDefaultHTML);
        }, 6000)
    }

    return {
        init: function() {
            if (getCookie('mallhabana_stock_redirection')) {
                showAlertMessage(getCookie('mallhabana_stock_redirection'), 'info');
            }
        },
    };

}();

jQuery(document).ready(function() {
    MallHabanaFront.init();
});