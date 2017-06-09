<section class="component component-account-licenses-upgrades">
  <h4 class="component-account-heading"><i class="fa fa-map-signs" aria-hidden="true"></i> Upgrades</h4>

  <table id="edd_sl_license_upgrades" class="edd-table">

    <thead>
      <tr class="edd_purchase_row">
        <th class="edd_purchase_id">Available Upgrade</th>
        <th class="edd_purchase_date">Price</th>
        <th class="edd_purchase_amount">Link</th>
      </tr>
    </thead>

    <tbody>
  <?php


  $sl = edd_software_licensing();

  if (empty($sl)) {
    echo "<pre>";
    print_r($sl);
    echo "</pre>";
  }


  // $license_keys = $sl->get_licenses_of_purchase($user_purchase->ID);


  $license_keys = edd_software_licensing()->get_license_keys_of_user( get_current_user_id(), 0, 'any', false );

  foreach ($license_keys as $key => $license) {



  $upgrades = edd_sl_get_license_upgrades( $license->ID );

        // echo "<pre>";
        // print_r($upgrades);
        // echo "</pre>";

        // echo edd_get_checkout_uri();

        // $url         = home_url();
      	// $download_id = edd_software_licensing()->get_download_id( $license->ID );
      	// $upgrades    = edd_sl_get_upgrade_paths( $download_id );
        //
      	// if( is_array( $upgrades ) && isset( $upgrades[ $upgrade_id ] ) ) {
        //
      	// 	$url = add_query_arg( array(
      	// 		'edd_action' => 'sl_license_upgrade',
      	// 		'license_id' => $license->ID,
      	// 		'upgrade_id' => $upgrade_id
      	// 	), edd_get_checkout_uri() );
        //
      	// }

         ?>


         <?php if ( $upgrades ) : ?>
           <?php foreach ( $upgrades as $upgrade_id => $upgrade ) : ?>

  <?php


  $url         = home_url();
  $download_id = edd_software_licensing()->get_download_id( $license->ID );
  $upgrades    = edd_sl_get_upgrade_paths( $download_id );

  if( is_array( $upgrades ) && isset( $upgrades[ $upgrade_id ] ) ) {

  	$url = add_query_arg( array(
  		'edd_action' => 'sl_license_upgrade',
  		'license_id' => $license->ID,
  		'upgrade_id' => $upgrade_id
  	), edd_get_checkout_uri() );


    // echo "<br>";

  }



  ?>






     <tr class="edd_sl_license_row">
       <?php do_action( 'edd_sl_license_upgrades_row_start', $license->ID ); ?>
       <td>
         <?php echo get_the_title( $upgrade['download_id'] ); ?>
         <?php if( isset( $upgrade['price_id'] ) && edd_has_variable_prices( $upgrade['download_id'] ) ) : ?>
           - <?php echo edd_get_price_option_name( $upgrade['download_id'], $upgrade['price_id'] ); ?>
         <?php endif; ?>
       </td>
       <td><?php echo edd_currency_filter( edd_sanitize_amount( edd_sl_get_license_upgrade_cost( $license->ID, $upgrade_id ) ) ); ?></td>
       <td><a href="<?php  echo $url; ?>" title="<?php esc_attr_e( 'Upgrade License', 'edd_sl' ); ?>"><?php _e( 'Upgrade License', 'edd_sl' ); ?></a></td>
       <?php do_action( 'edd_sl_license_upgrades_row_end', $license->ID ); ?>
     </tr>
   <?php endforeach; ?>
  <?php else: ?>
   <tr class="edd_sl_license_row">
     <?php do_action( 'edd_sl_license_upgrades_row_start', $license->ID ); ?>
     <td colspan="3"><?php _e( 'No upgrades available for this license', 'edd_sl' ); ?></td>
     <?php do_action( 'edd_sl_license_upgrades_row_end', $license->ID ); ?>
   </tr>
  <?php endif; ?>

  <?php } ?>

</tbody>
  </table>
</section>
