(function($) {
  $(document).ready(function () {
    reepay.configure(drupalSettings.reepay.reepayApi);

    window.$ = $;
    reepay.validate.formatCardNumber('input[data-reepay="number"]', function (card) {
      if ((card.type == 'default') || (card.type == 'unknown')) {
        // The cardnumber is invalid or unknown
        $('input[data-reepay="number"]').addClass('error');
      } else {
        // The cardnumber is valid and has a known cardType
        $('input[data-reepay="number"].error').removeClass('error');
        $('input[data-reepay="number"]').parent().find('.error-message').remove();
      }
    });

    // Handle submit
    $('form.commerce-checkout-flow').on('submit', function (event) {
      var form = this;
      event.preventDefault();
      // Clear any previous errors.
      $('.error', form).removeClass('error');
      $('.error-message', form).remove();
      reepay.token(form, function (err, token) {
        if (err) {
          // These errors are related to a field.
          var field_error_codes = [
            'validation',
            'invalid-parameter',
            'invalid-request',
            'api-error',
          ];
          if (field_error_codes.indexOf(err.code) > -1) {
            $(err.fields).each(function(key, val) {
              $('[data-reepay="' + val + '"]', form).addClass('error');
              $('[data-reepay="' + val + '"]', form).parent().append('<span class="error-message">' + Drupal.t(err.message) + '</span>');
            });
          }
          else {
            // Generic errors not related to a field.
            $(form).append('<span class="error-message">' + Drupal.t(err.message) + '</span>');
          }
        } else {
          // At this point we have a valid reepay token which has been
          // put in the form, so we can submit it, but first remove all
          // sensitive data so we don't send credit card, expiration and
          // cvv to Drupal.
          $('input[data-reepay="number"]').val(token.masked_card);
          $('input[data-reepay="month"]').val('');
          $('input[data-reepay="year"]').val('');
          $('input[data-reepay="cvv"]').val('');
          $(form).attr('action', drupalSettings.reepay.return);
          form.submit();
        }
      });
    });
  });
})(jQuery);
