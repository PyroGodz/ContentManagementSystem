"use strict";

function agrLoad() {
  const recaptcha = document.getElementsByClassName('agr-recaptcha-wrapper');
  for (let i = 0; i < recaptcha.length; i++) {
    grecaptcha.render(recaptcha.item(i), {
      sitekey: agrRecaptcha.site_key
    });
  }
}
function agrV3() {
  grecaptcha.execute(agrRecaptcha.site_key, {
    action: 'validate_recaptchav3'
  }).then(function (token) {
    document.querySelectorAll('.g-recaptcha-response').forEach(function (elem) {
      elem.value = token;
    });
  });
}