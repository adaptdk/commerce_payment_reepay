(function($) {
    $(document).ready(function () {
        console.log(drupalSettings);
        reepay.configure(drupalSettings.reepay.reepayApi);
    });
    $('.multistep-interflora').on('submit', function (event) {
        var form = this;
        event.preventDefault();
        reepay.token(form, function (err, token) {
            if (err) {
                console.log(err);
                // handle error using err.code and err.fields
            } else {
                // The reepay-token field has automaticly been filled with the token
                form.submit();
            }
        });
    });
})(jQuery);
