import reduce from 'lodash/reduce';

/*

Select Element Contents

*/
function selectElementContents(el) {
  var range = document.createRange();
  range.selectNodeContents(el);
  var sel = window.getSelection();
  sel.removeAllRanges();
  sel.addRange(range);
}

/*

Copy to Clipboard

*/
function copyToClipboard() {
  var clipboard = new Clipboard('.copy-trigger');

  clipboard.on('success', function (e) {
    var $notice = jQuery('.notice-copy');

    $notice.addClass('is-notifying');

    setTimeout(function () {
      $notice.removeClass('is-notifying');
      e.clearSelection();
    }, 2500);

    selectElementContents(jQuery(e.trigger)[0]);
  });

  clipboard.on('error', function (e) {
    console.error('Copy error: ', e);
  });
}

/*

Selects text

*/
function selectText(element) {
  if (document.body.createTextRange) {
    var range = document.body.createTextRange();
    range.moveToElementText(element);
    range.select();
  } else if (window.getSelection) {
    var selection = window.getSelection();
    var range = document.createRange();
    range.selectNodeContents(element);
    selection.removeAllRanges();
    selection.addRange(range);
  }
}

/*

Show Loader

*/
function showLoader($form) {
  $form.find('.spinner').addClass('is-visible');
}

/*

Hide Loader

*/
function hideLoader($form) {
  $form.find('.spinner').removeClass('is-visible');
}

/*

Disable Form

*/
function disableForm($form) {
  $form.find('input, select, textarea, label, label + span, .form-note').addClass('is-disabled');
  $form.addClass('is-submitting');
}

/*

Enable Form

*/
function enableForm($form) {
  $form.find('input, select, textarea, label, label + span, .form-note').removeClass('is-disabled');
  $form.removeClass('is-submitting');
}

/*

General Disable

*/
function disable($element) {
  $element.addClass('is-disabled');
}

/*

General Enable

*/
function enable($element) {
  $element.removeClass('is-disabled');
}

/*

Do Scrolling

*/
function doScrolling(element, duration) {
  var startingY = window.pageYOffset;
  var elementY = getElementY(element);
  // If element is close to page's bottom then window will scroll only to some position above the element.
  var targetY =
    document.body.scrollHeight - elementY < window.innerHeight
      ? document.body.scrollHeight - window.innerHeight
      : elementY;
  var diff = targetY - startingY;
  // Easing function: easeInOutCubic
  // From: https://gist.github.com/gre/1650294
  var easing = function (t) {
    return t < 0.5 ? 4 * t * t * t : (t - 1) * (2 * t - 2) * (2 * t - 2) + 1;
  };
  var start;

  if (!diff) return;

  // Bootstrap our animation - it will get called right before next frame shall be rendered.
  window.requestAnimationFrame(function step(timestamp) {
    if (!start) start = timestamp;
    // Elapsed miliseconds since start of scrolling.
    var time = timestamp - start;
    // Get percent of completion in range [0, 1].
    var percent = Math.min(time / duration, 1);
    // Apply the easing.
    // It can cause bad-looking slow frames in browser performance tool, so be careful.
    percent = easing(percent);

    window.scrollTo(0, startingY + diff * percent);

    // Proceed with animation as long as we wanted it to.
    if (time < duration) {
      window.requestAnimationFrame(step);
    }
  });
}

/*

Has Form Value

*/
function hasValue(element) {
  return (
    jQuery(element).filter(function () {
      return jQuery(this).val();
    }).length > 0
  );
}

/*

Reduces form data to a single object

*/
function reduceFormData($form) {
  return reduce(
    $form.serializeArray(),
    function (hash, value) {
      var key = value['name'];
      hash[key] = value['value'];
      return hash;
    },
    {}
  );
}

/*

Clear Form Fields

*/
function clearFormFields($form) {
  $form.find('input').blur();
  $form[0].reset();
  $form.find('input').removeClass('valid');
  $form.find('.is-valid').remove();
}

export {
  showLoader,
  hideLoader,
  disableForm,
  enableForm,
  enable,
  disable,
  hasValue,
  reduceFormData,
  clearFormFields,
  selectText,
  copyToClipboard,
};
