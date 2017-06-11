<section class="component component-account-licenses">
  <h4 class="component-account-heading"><i class="fa fa-key" aria-hidden="true"></i> Licenses</h4>

  <?php

  $sl = edd_software_licensing();

  $license_keys = edd_software_licensing()->get_license_keys_of_user( get_current_user_id(), 0, 'any', false );

  if ( $license_keys ) : ?>

  <table id="edd_sl_license_keys2" class="edd_sl_table">
    <thead>
      <tr class="edd_sl_license_row">
        <th class="edd_sl_status"><?php _e( 'Status', 'edd_sl' ); ?></th>
        <th class="edd_sl_limit"><?php _e( 'Activations', 'edd_sl' ); ?></th>
        <th class="edd_sl_expiration"><?php _e( 'Expiration', 'edd_sl' ); ?></th>
        <?php do_action('edd_sl_license_header_after'); ?>
      </tr>
    </thead>

    <?php foreach ( $license_keys as $license ) : ?>

      <tr class="edd_sl_license_row">
        <td class="edd_sl_license_status edd-sl-<?php echo $sl->get_license_status( $license->ID ); ?>"><?php echo $sl->license_status( $license->ID ); ?></td>
        <td><span class="edd_sl_limit_used"><?php echo $sl->get_site_count( $license->ID ); ?></span><span class="edd_sl_limit_sep">&nbsp;/&nbsp;</span><span class="edd_sl_limit_max"><?php echo $sl->license_limit( $license->ID ); ?></span></td>
        <td>
        <?php if ( method_exists( $sl, 'is_lifetime_license' ) && $sl->is_lifetime_license( $license->ID ) ) : ?>
          <?php _e( 'Lifetime', 'edd_sl' ); ?>
        <?php else: ?>
          <?php echo date_i18n( 'F j, Y', $sl->get_license_expiration( $license->ID ) ); ?>
        <?php endif; ?>
        <?php if( edd_sl_renewals_allowed() && $license->post_parent == 0 ) : ?>
          <?php if( 'expired' === edd_software_licensing()->get_license_status( $license->ID ) && edd_software_licensing()->can_renew( $license->ID ) ) : ?>
            <span class="edd_sl_key_sep">&nbsp;&ndash;&nbsp;</span>
            <a href="<?php echo edd_software_licensing()->get_renewal_url( $license->ID ); ?>" title="<?php esc_attr_e( 'Renew license', 'edd_sl' ); ?>"><?php _e( 'Renew license', 'edd_sl' ); ?></a>
          <?php elseif( ! edd_software_licensing()->is_lifetime_license( $license->ID ) && edd_software_licensing()->can_extend( $license->ID ) ) : ?>
            <span class="edd_sl_key_sep">&nbsp;&ndash;&nbsp;</span>
            <a href="<?php echo edd_software_licensing()->get_renewal_url( $license->ID ); ?>" title="<?php esc_attr_e( 'Extend license', 'edd_sl' ); ?>"><?php _e( 'Extend license', 'edd_sl' ); ?></a>
          <?php endif; ?>
        <?php endif; ?>
        </td>
        <?php do_action( 'edd_sl_license_row_end', $license->ID ); ?>
      </tr>
    <?php endforeach; ?>
  </table>

	<table id="edd_sl_license_keys" class="edd_sl_table">
		<thead>
			<tr class="edd_sl_license_row">
				<th class="edd_sl_key"><?php _e( 'License Key', 'edd_sl' ); ?></th>
				<th class="edd_sl_type"><?php _e( 'License Type', 'edd_sl' ); ?></th>
			</tr>
		</thead>
		<?php foreach ( $license_keys as $license ) : ?>
			<tr class="edd_sl_license_row">

        <td>
          <span class="view-key-wrapper">

            <input id="license-key" readonly type="text" class="btn-copy edd_sl_license_key" value="<?php echo esc_attr( $sl->get_license_key( $license->ID ) ); ?>" data-clipboard-text="<?php echo esc_attr( $sl->get_license_key( $license->ID ) ); ?>" />
            <small class="notice-inline">Copied!</small>
            <i class="fa fa-key" aria-hidden="true"></i>

          </span>
        </td>

        <td>
					<?php
					$download_id = $sl->get_download_id( $license->ID );
					$price_id    = $sl->get_price_id( $license->ID );

					echo get_the_title( $download_id ); ?>
					<?php if( '' !== $price_id ) : ?>
						<span class="edd_sl_license_price_option">&ndash;&nbsp;<?php echo edd_get_price_option_name( $download_id, $price_id ); ?></span>
					<?php endif; ?>
				</td>


			</tr>
		<?php endforeach; ?>
	</table>

  <?php else : ?>
  	<p class="edd_sl_no_keys"><?php _e( 'There are no license keys for this purchase', 'edd_sl' ); ?></p>
  <?php endif;?>

</section>
