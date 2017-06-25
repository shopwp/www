import '../../css/app/app.scss';

import { onShopifyAuth } from './security/security';
import { initPlugins } from './plugins/plugins';
import { initForms } from './forms/forms';
import { initMailinglist } from './mailinglist/mailinglist';
import { initCheckout } from './checkout/checkout';
import { initAccount } from './account/account';
import { initDocs } from './docs/docs';
import { initMobile } from './mobile/mobile';

(function($) {
  "use strict";

  $(function() {

    if (!$('body').hasClass('is-mobile')) {
      Pace.restart();

    } else {
      Pace.stop();
      
    }

    initPlugins($);
    initForms($);
    initMailinglist($);
    initCheckout($);
    initAccount($);
    initDocs($);

    initMobile($);

    if (window.location.pathname === '/auth') {
      onShopifyAuth($);
    }

  });

})(jQuery);
