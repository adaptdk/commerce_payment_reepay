(function($) {
  $(document).ready(function() {
    reepay.configure(drupalSettings.reepay.reepayApi);

    var inputCardNumber = $('input[data-reepay="number"]');
    var inputMonth = $('input[data-reepay="month"]');
    var inputYear = $('input[data-reepay="year"]');
    var inputCvv = $('input[data-reepay="cvv"]');

    var classError = 'error';

    var cardExpiryData = {
      month: '',
      year: '',
    }

    // card expiry validation
    function cardExpiryValidation(cardExpiryData) {
      if (cardExpiryData.month.length > 0 && cardExpiryData.year.length > 0) {
        reepay.validate.expiry(cardExpiryData.month, cardExpiryData.year);

        if (!reepay.validate.expiry(cardExpiryData.month, cardExpiryData.year)) {
          inputMonth.addClass(classError);
          inputYear.addClass(classError);
        } else {
          inputMonth.removeClass(classError);
          inputYear.removeClass(classError);
        }
      }
    }

    // Card number
    inputCardNumber.blur(function(e) {
      var inputValue = e.target.value;

      if (!reepay.validate.cardNumber(inputValue)) {
        inputCardNumber.addClass(classError);
      } else {
        inputCardNumber.removeClass(classError);
      }
    });

    // Card expiry - Month
    inputMonth.blur(function(e) {
      var inputValue = e.target.value;

      if (inputValue.length > 0) {
        cardExpiryData.month = inputValue;
        cardExpiryValidation(cardExpiryData);
      } else {
        cardExpiryData.month = '';
      }

      if (Number(inputValue) === 0 || Number(inputValue) > 12) {
        inputMonth.addClass(classError);
      } else {
        inputMonth.removeClass(classError);
      }

      console.log(cardExpiryData);
    });

    // // Card expiry - Year
    inputYear.blur(function(e) {
      var inputValue = e.target.value;

      if (inputValue.length > 0) {
        cardExpiryData.year = inputValue;
        cardExpiryValidation(cardExpiryData);
      } else {
        cardExpiryData.year = '';
      }

      if (inputValue.length < 2) {
        inputYear.addClass(classError);
      } else {
        inputYear.removeClass(classError);
      }

      console.log(cardExpiryData);
    });


    // Card number
    inputCvv.blur(function(e) {
      var inputValue = e.target.value;

      if (!reepay.validate.cvv(inputValue)) {
        inputCvv.addClass(classError);
      } else {
        inputCvv.removeClass(classError);
      }
    });

    window.$ = $;
    reepay.validate.formatCardNumber('input[data-reepay="number"]', function(card) {
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
    $('form.commerce-checkout-flow').on('submit', function(event) {
      var form = this;
      event.preventDefault();
      // Clear any previous errors.
      $('.error', form).removeClass('error');
      $('.error-message', form).remove();
      reepay.token(form, function(err, token) {
        if (err) {
          console.log(err);
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
              $('[data-reepay="' + val + '"]', form).parent().append('<span class="error-message">' + Drupal.t(
                err.message) + '</span>');
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
          inputCardNumber.val(token.masked_card);
          inputMonth.val('');
          inputYear.val('');
          inputCvv.val('');
          $(form).attr('action', drupalSettings.reepay.return);
          form.submit();
        }
      });
    });
  });
})(jQuery);
