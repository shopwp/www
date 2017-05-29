import '../../css/app/app.scss';

import { onShopifyAuth } from './security/security';
import { initPlugins } from './plugins/plugins';
import { initForms } from './forms/forms';
import { initMailinglist } from './mailinglist/mailinglist';
import { initCheckout } from './checkout/checkout';
import { initAccount } from './account/account';
import { initDocs } from './docs/docs';

(function($) {
  "use strict";

  $(function() {

    initPlugins($);
    initForms($);
    initMailinglist($);
    initCheckout($);
    initAccount($);
    initDocs($);

    // onShopifyAuth($);

  });

})(jQuery);
