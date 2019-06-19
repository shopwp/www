<?php

$customer = new EDD_Customer(get_current_user_id(), true );

$hash = md5( strtolower( trim($customer->email) ) );

?> 
<div class="l-row component component-account-user-info">
     <div class="l-col account-user-info-image">
        <img src="https://www.gravatar.com/avatar/<?php echo $hash; ?>?s=200" alt="" class="user-img" />
     </div>
     <div class="l-col account-user-info-contact">
        <div class="user-info">
            <h5><?php echo $customer->name; ?></h5>
            <h5><?php echo $customer->email; ?></h5>
            <a href="<?php echo wp_logout_url('/login'); ?>" class="link-account">Logout <i class="far fa-sign-out"></i></a>
         </div>
     </div>

  </div>