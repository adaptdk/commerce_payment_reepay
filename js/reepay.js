(function($) {
    $(document).ready(function () {
        reepay.configure(drupalSettings.reepay.reepayApi);
    });
    $('form.commerce-checkout-flow').on('submit', function (event) {
        var form = this;
        event.preventDefault();
        $('.error', form).removeClass('error');
        $('.error-message', form).remove();
        reepay.token(form, function (err, token) {
            if (err) {
                $(err.fields).each(function(key, val) {
                   $('[data-reepay="' + val + '"]', form).addClass('error');
                   $('[data-reepay="' + val + '"]', form).parent().append('<span class="error-message">Please enter a valid value</span>')
                });
                // handle error using err.code and err.fields
            } else {
                // The reepay-token field has automaticly been filled with the token
                form.submit();
            }
        });
    });
})(jQuery);
