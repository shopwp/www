<?php 

global $edd_receipt_args;

$order 				= edd_get_order( $edd_receipt_args['id'] );
$cart_items 		= edd_get_payment_meta_downloads($order->id);
$download_files 	= edd_get_download_files( $cart_items[0]['id'], $cart_items[0]['options']['price_id'] );

?>

<div id="edd_checkout_wrap" class="edd-checkout">
	<div class="shopwp-checkout-inner">
		<div class="order-confirm-col" id="edd_checkout_cart_form">
			<h2 class="checkout-subheading">🤔 Next steps:</h2>
			<ol class="order-next">
				<li>
					<p class="order-next-label">
						<a href="<?= $download_files[0]['file']; ?>" target="_blank" download>Download <?= $download_files[0]['name']; ?></a>
					</p>
					<p class="order-next-description">
						<span>The latest version of ShopWP is 8.0.6</span>
					</p>	
				</li>
				<li>
					<p class="order-next-label">
						Join the private <a href="https://join.slack.com/t/wpshopify/shared_invite/zt-p3qsqzb5-jrq9n2kY90MgCGALvYoN4Q" target="_blank">Slack channel</a>
					</p>
					<p class="order-next-description">
						<span>Come ask any questions you have! Users in Slack receive priority support.</span>
					</p>
				</li>
				<li>
					<p class="order-next-label">
						Access your <a href="/account" target="_blank">ShopWP account</a>
					</p>
					<p class="order-next-description">
						<span>Download the plugin(s), change your info, and manage your subscriptions in one place.</span>
					</p>
				</li>
				
			</ol>
			<iframe width="560" height="315" src="https://www.youtube.com/embed/YypIUgOvoqA?si=6GWanCDsDz8Y6Tca" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
		</div>			
		<div id="edd_checkout_form_wrap" class="order-confirm-col">
			<a href="/" class="logo">
				<svg width="140" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" id="Layer_1" x="0" y="0" style="enable-background:new 0 0 733 191" version="1.1" viewBox="0 0 733 191"><style>.st0{fill:#000}</style><circle cx="91.6" cy="95.4" r="88.7" class="st0"></circle><path d="M78 54.8c2.7-6.8 8.6-11.5 15.4-11.5S106 48 108.8 54.8h7.2c-3.1-11.3-12-19.4-22.5-19.4S74.1 43.6 71 54.8h7zM138.4 65.9H46.6c-1.9 21.4-3.6 42.8-4.9 64.2.3 2.7 2.1 7.4 9.8 8H133.3c7.7-.6 9.5-5.2 9.8-8-1.1-21.5-2.8-42.8-4.7-64.2zm-18.4 38c-2.1 6.6-5.9 12.1-11.2 15.8-4.8 3.5-10.6 5.3-16.4 5.3h-.2c-5.7-.1-11.4-1.9-16-5.3-5.2-3.8-9-9.2-11.2-15.8l-.6-1.9h12l.3.7c2.8 6.9 8.9 11.3 15.9 11.3h.3c6.9-.1 12.9-4.4 15.6-11.3l.3-.7h12l-.8 1.9z"></path><path d="M251.1 133.6c-9.9 0-21-3.3-30.4-10.7l8.6-13.2c7.7 5.6 15.7 8.5 22.4 8.5 5.8 0 8.5-2.1 8.5-5.3v-.3c0-4.4-6.9-5.8-14.7-8.2-9.9-2.9-21.2-7.5-21.2-21.3v-.3c0-14.4 11.6-22.5 25.9-22.5 9 0 18.8 3 26.5 8.2l-7.7 14c-7-4.1-14-6.6-19.2-6.6-4.9 0-7.4 2.1-7.4 4.9v.2c0 4 6.7 5.8 14.4 8.5 9.9 3.3 21.4 8.1 21.4 21v.3c0 15.7-11.7 22.8-27.1 22.8zM337.5 132.3V92.8c0-9.5-4.5-14.4-12.2-14.4s-12.6 4.9-12.6 14.4v39.5h-20.1V35.8h20.1v35.7c4.6-6 10.6-11.4 20.8-11.4 15.2 0 24.1 10.1 24.1 26.3v45.9h-20.1zM409.8 133.9c-22 0-38.2-16.3-38.2-36.6V97c0-20.4 16.4-36.9 38.5-36.9 22 0 38.2 16.3 38.2 36.6v.3c0 20.4-16.4 36.9-38.5 36.9zM428.5 97c0-10.4-7.5-19.6-18.6-19.6-11.5 0-18.4 8.9-18.4 19.3v.3c0 10.4 7.5 19.6 18.6 19.6 11.5 0 18.4-8.9 18.4-19.3V97zM505.1 133.6c-10.7 0-17.3-4.9-22.1-10.6v30.4h-20.1v-92H483v10.2c4.9-6.6 11.6-11.5 22.1-11.5 16.5 0 32.3 13 32.3 36.6v.3c-.1 23.7-15.5 36.6-32.3 36.6zm12.1-36.9c0-11.8-7.9-19.6-17.3-19.6s-17.2 7.8-17.2 19.6v.3c0 11.8 7.8 19.6 17.2 19.6 9.4 0 17.3-7.7 17.3-19.6v-.3zM623.5 132.9H618l-20.1-57.8-20.2 57.8h-5.6l-24.7-68h7.3l20.4 59.3 20.4-59.5h5.2l20.4 59.5 20.4-59.3h7l-25 68zM695.3 133.9c-13.5 0-22.2-7.7-27.8-16.1v35.7H661V64.9h6.5V80c5.8-8.9 14.4-16.7 27.8-16.7 16.3 0 32.8 13.1 32.8 35v.3c0 22-16.6 35.3-32.8 35.3zm25.8-35.3c0-17.7-12.3-29.1-26.5-29.1-14 0-27.5 11.8-27.5 29v.3c0 17.3 13.5 29 27.5 29 14.7 0 26.5-10.7 26.5-28.8v-.4z" class="st0"></path></svg> 
			</a>

			<div class="order-top">
				<h1 class="checkout-heading"><svg class="absolute h-6 w-6" width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M0 12.2174C0 5.58999 5.37258 0.217407 12 0.217407V0.217407C18.6274 0.217407 24 5.58999 24 12.2174V12.2174C24 18.8448 18.6274 24.2174 12 24.2174V24.2174C5.37258 24.2174 0 18.8448 0 12.2174V12.2174Z" fill="#bcf0bb"></path>
				<path d="M15.7707 8.69502L10.246 14.1386L8.22932 12.1309C8.12429 12.0481 7.95624 12.0481 7.87221 12.1309L7.26302 12.7311C7.17899 12.8139 7.17899 12.9795 7.26302 13.083L10.0779 15.8358C10.1829 15.9393 10.33 15.9393 10.435 15.8358L16.737 9.62643C16.821 9.54364 16.821 9.37805 16.737 9.27456L16.1278 8.69502C16.0438 8.59153 15.8757 8.59153 15.7707 8.69502Z" fill="#000"></path>
				</svg>Thank you!</h1>

				<p>Your order is confirmed. You will receive an email containing a receipt and login credentials for your ShopWP account.</p>

				<div class="order-cta l-row">
					<a href="/account" target="_blank" class="btn btn-s">Go to account</a>
					<a href="<?php echo esc_url( edd_invoices_get_invoice_form_url( $order->ID ) ); ?>" target="_blank" class="link">Download invoice</a>
				</div>
				
			</div>
			

			<?php

			// Display a notice if the order was not found in the database.
			if ( ! $order ) : ?>

				<div class="edd_errors edd-alert edd-alert-error">
					<?php esc_html_e( 'The specified receipt ID appears to be invalid.', 'easy-digital-downloads' ); ?>
				</div>

				<?php

				return;

			endif;

			/**
			 * Allows additional output before displaying the receipt table.
			 *
			 * @since 3.0
			 *
			 * @param \EDD\Orders\Order $order          Current order.
			 * @param array             $edd_receipt_args [edd_receipt] shortcode arguments.
			 */
			do_action( 'edd_order_receipt_before_table', $order, $edd_receipt_args );
			?>

			<div class="l-col">

					<table id="edd_purchase_receipt" class="edd-table">
					<tbody>

					<?php do_action( 'edd_order_receipt_before', $order, $edd_receipt_args ); ?>

						<?php if ( filter_var( $edd_receipt_args['payment_id'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
						<tr>
							<td><strong><?php echo esc_html_x( 'Order #', 'heading', 'easy-digital-downloads' ); ?>:</strong></td>
							<td><?php echo esc_html( $order->get_number() ); ?></td>
						</tr>
						<?php endif; ?>

						<tr>
							<td class="edd_receipt_payment_status"><strong><?php esc_html_e( 'Order Status', 'easy-digital-downloads' ); ?>:</strong></td>
							<td class="edd_receipt_payment_status <?php echo esc_attr( strtolower( $order->status ) ); ?>"><?php echo esc_html( edd_get_status_label( $order->status ) ); ?></td>
						</tr>

						<?php if ( filter_var( $edd_receipt_args['payment_key'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
							<tr>
								<td><strong><?php esc_html_e( 'Payment Key', 'easy-digital-downloads' ); ?>:</strong></td>
								<td><?php echo esc_html( $order->payment_key ); ?></td>
							</tr>
						<?php endif; ?>

						<?php if ( filter_var( $edd_receipt_args['payment_method'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
							<tr>
								<td><strong><?php esc_html_e( 'Payment Method', 'easy-digital-downloads' ); ?>:</strong></td>
								<td><?php echo esc_html( edd_get_gateway_checkout_label( $order->gateway ) ); ?></td>
							</tr>
						<?php endif; ?>
						<?php if ( filter_var( $edd_receipt_args['date'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
						<tr>
							<td><strong><?php esc_html_e( 'Date', 'easy-digital-downloads' ); ?>:</strong></td>
							<td><?php echo esc_html( edd_date_i18n( EDD()->utils->date( $order->date_created, null, true )->toDateTimeString() ) ); ?></td>
						</tr>
						<?php endif; ?>

						<?php if ( filter_var( $edd_receipt_args['price'], FILTER_VALIDATE_BOOLEAN ) && $order->subtotal > 0 ) : ?>
							<tr>
								<td><strong><?php esc_html_e( 'Subtotal', 'easy-digital-downloads' ); ?>:</strong></td>
								<td>
									<?php echo esc_html( edd_payment_subtotal( $order->id ) ); ?>
								</td>
							</tr>
						<?php endif; ?>

						<?php
						if ( filter_var( $edd_receipt_args['discount'], FILTER_VALIDATE_BOOLEAN ) ) :
							$order_discounts = $order->get_discounts();
							if ( $order_discounts ) :
								$label = _n( 'Discount', 'Discounts', count( $order_discounts ), 'easy-digital-downloads' );
								?>
								<tr>
									<td colspan="2"><strong><?php echo esc_html( $label ); ?>:</strong></td>
								</tr>
								<?php
								foreach ( $order_discounts as $order_discount ) {
									$label = $order_discount->description;
									if ( 'percent' === edd_get_discount_type( $order_discount->type_id ) ) {
										$rate   = edd_format_discount_rate( 'percent', edd_get_discount_amount( $order_discount->type_id ) );
										$label .= "&nbsp;({$rate})";
									}
									?>
									<tr>
										<td><?php echo esc_html( $label ); ?></td>
										<td><?php echo esc_html( edd_display_amount( edd_negate_amount( $order_discount->total ), $order->currency ) ); ?></td>
									</tr>
									<?php
								}
								?>
								</tr>
							<?php endif; ?>
						<?php endif; ?>

						<?php
						$fees = $order->get_fees();
						if ( ! empty( $fees ) ) :
							?>
							<tr>
								<td colspan="2"><strong><?php echo esc_html( _n( 'Fee', 'Fees', count( $fees ), 'easy-digital-downloads' ) ); ?>:</strong></td>
							</tr>
							<?php
							foreach ( $fees as $fee ) :
								$label = __( 'Fee', 'easy-digital-downloads' );
								if ( ! empty( $fee->description ) ) {
									$label = $fee->description;
								}
								?>
								<tr>
									<td><span class="edd_fee_label"><?php echo esc_html( $label ); ?></span></td>
									<td><span class="edd_fee_amount"><?php echo esc_html( edd_display_amount( $fee->subtotal, $order->currency ) ); ?></span></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>

						<?php if ( $order->tax > 0 ) : ?>
							<tr>
								<td><strong><?php esc_html_e( 'Tax', 'easy-digital-downloads' ); ?>:</strong></td>
								<td><?php echo esc_html( edd_payment_tax( $order->id ) ); ?></td>
							</tr>
						<?php endif; ?>
						<?php
						$credits = $order->get_credits();
						if ( $credits ) {
							?>
							<tr>
								<td colspan="2"><strong><?php echo esc_html( _n( 'Credit', 'Credits', count( $credits ), 'easy-digital-downloads' ) ); ?>:</strong></td>
							</tr>
							<?php
							foreach ( $credits as $credit ) {
								$label = __( 'Credit', 'easy-digital-downloads' );
								if ( ! empty( $credit->description ) ) {
									$label = $credit->description;
								}
								?>
								<tr>
									<td><?php echo esc_html( $label ); ?></td>
									<td><?php echo esc_html( edd_display_amount( edd_negate_amount( $credit->total ), $order->currency ) ); ?></td>
								</tr>
								<?php
							}
						}
						?>

						<?php if ( filter_var( $edd_receipt_args['price'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
							<tr class="order-total-row">
								<td><strong><?php esc_html_e( 'Total', 'easy-digital-downloads' ); ?>:</strong></td>
								<td><?php echo esc_html( edd_payment_amount( $order ) ); ?></td>
							</tr>
						<?php endif; ?>

						<?php
						/**
						 * Fires at the end of the order receipt `tbody`.
						 *
						 * @since 3.0
						 * @param \EDD\Orders\Order $order          Current order.
						 * @param array             $edd_receipt_args [edd_receipt] shortcode arguments.
						 */
						// do_action( 'edd_order_receipt_after', $order, $edd_receipt_args );
						?>
					</tbody>
				</table>

				<h3>License Details</h3>
				<table id="order-license-table">
					<tbody>
						<?php
						/**
						 * Fires at the end of the order receipt `tbody`.
						 *
						 * @since 3.0
						 * @param \EDD\Orders\Order $order          Current order.
						 * @param array             $edd_receipt_args [edd_receipt] shortcode arguments.
						 */
						do_action( 'edd_order_receipt_after', $order, $edd_receipt_args );
						?>
						</tbody>
						</table>
			</div>



			<?php
			/**
			 * Fires after the order receipt table.
			 *
			 * @since 3.0
			 * @param \EDD\Orders\Order $order          Current order.
			 * @param array             $edd_receipt_args [edd_receipt] shortcode arguments.
			 */
			do_action( 'edd_order_receipt_after_table', $order, $edd_receipt_args );

			if ( ! filter_var( $edd_receipt_args['products'], FILTER_VALIDATE_BOOLEAN ) ) {
				return;
			}
			$order_items = $order->get_items();
			if ( empty( $order_items ) ) {
				return;
			}
			?>

			<h3><?php echo esc_html( apply_filters( 'edd_payment_receipt_products_title', __( 'Products', 'easy-digital-downloads' ) ) ); ?></h3>

			<table id="edd_purchase_receipt_products" class="edd-table">

				<thead>
					
					<th><?php esc_html_e( 'Name', 'easy-digital-downloads' ); ?></th>
					
					<?php if ( edd_use_skus() ) { ?>
						<th><?php esc_html_e( 'SKU', 'easy-digital-downloads' ); ?></th>
					<?php } ?>
					
					<?php if ( edd_item_quantities_enabled() ) : ?>
						<th><?php esc_html_e( 'Quantity', 'easy-digital-downloads' ); ?></th>
					<?php endif; ?>

					<th><?php esc_html_e( 'Price', 'easy-digital-downloads' ); ?></th>

				</thead>

				<tbody>

					<?php 
						
						foreach ( $order_items as $key => $item ) : ?>

						<?php

						// Skip this item if we can't view it.
						if ( ! apply_filters( 'edd_user_can_view_receipt_item', true, $item ) ) {
							continue;
						}
						
						?>

						<tr>
							<td>
								<?php $download_files = edd_get_download_files( $item->product_id, $item->price_id ); ?>

								<div class="edd_purchase_receipt_product_name">
									<?php

									$product_name = esc_html($item->product_name);

									$newphrase = str_replace('WP Shopify', 'ShopWP Pro', $product_name);

									echo $newphrase;

									if ( ! empty( $item->status ) && 'complete' !== $item->status ) {
										echo ' &ndash; ' . esc_html( edd_get_status_label( $item->status ) );
									}
									?>
								</div>
								
								<?php

								$notes = edd_get_product_notes( $item->product_id );

								if ( ! empty( $notes ) ) : ?>
									<div class="edd_purchase_receipt_product_notes"><?php echo wp_kses_post( wpautop( $notes ) ); ?></div>
								<?php endif; ?>

								<?php if ( 'refunded' !== $item->status && edd_receipt_show_download_files( $item->product_id, $edd_receipt_args, $item ) ) : ?>
								<ul class="edd_purchase_receipt_files">
									<?php
									if ( ! empty( $download_files ) && is_array( $download_files ) ) :

										foreach ( $download_files as $filekey => $file ) :

											$filename = esc_html($file['file']);

											$stuff = explode('_pro/', $filename);

											?>
											<li class="edd_download_file">
												<a href="<?php echo esc_url( edd_get_download_file_url( $order, $order->email, $filekey, $item->product_id, $item->price_id ) ); ?>" class="edd_download_file_link"><?php echo $stuff[1]; ?></a>
											</li>
											<?php
											/**
											 * Fires at the end of the order receipt files list.
											 *
											 * @since 3.0
											 * @param int   $filekey          Index of array of files returned by edd_get_download_files() that this download link is for.
											 * @param array $file             The array of file information.
											 * @param int   $item->product_id The product ID.
											 * @param int   $order->id        The order ID.
											 */
											do_action( 'edd_order_receipt_files', $filekey, $file, $item->product_id, $order->id );
										endforeach;
									elseif ( edd_is_bundled_product( $item->product_id ) ) :
										$bundled_products = edd_get_bundled_products( $item->product_id, $item->price_id );

										foreach ( $bundled_products as $bundle_item ) :
											?>

											<li class="edd_bundled_product">
												<span class="edd_bundled_product_name"><?php echo esc_html( edd_get_bundle_item_title( $bundle_item ) ); ?></span>
												<ul class="edd_bundled_product_files">
													<?php
													$bundle_item_id       = edd_get_bundle_item_id( $bundle_item );
													$bundle_item_price_id = edd_get_bundle_item_price_id( $bundle_item );
													$download_files       = edd_get_download_files( $bundle_item_id, $bundle_item_price_id );

													if ( $download_files && is_array( $download_files ) ) :
														foreach ( $download_files as $filekey => $file ) :
															?>
															<li class="edd_download_file">
																<a href="<?php echo esc_url( edd_get_download_file_url( $order, $order->email, $filekey, $bundle_item, $bundle_item_price_id ) ); ?>" class="edd_download_file_link"><?php echo esc_html( edd_get_file_name( $file ) ); ?></a>
															</li>
															<?php
															/**
															 * Fires at the end of the order receipt bundled files list.
															 *
															 * @since 3.0
															 * @param int   $filekey          Index of array of files returned by edd_get_download_files() that this download link is for.
															 * @param array $file             The array of file information.
															 * @param int   $item->product_id The product ID.
															 * @param array $bundle_item      The array of information about the bundled item.
															 * @param int   $order->id        The order ID.
															 */
															do_action( 'edd_order_receipt_bundle_files', $filekey, $file, $item->product_id, $bundle_item, $order->id );
														endforeach;
													else :
														echo '<li>' . esc_html__( 'No downloadable files found for this bundled item.', 'easy-digital-downloads' ) . '</li>';
													endif;
													?>
												</ul>
											</li>
											<?php
										endforeach;

									else :
										echo '<li>' . esc_html( apply_filters( 'edd_receipt_no_files_found_text', __( 'No downloadable files found.', 'easy-digital-downloads' ), $item->product_id ) ) . '</li>';
									endif;
									?>
								</ul>
								<?php endif; ?>

								<?php
								/**
								 * Allow extensions to extend the product cell.
								 * @since 3.0
								 * @param \EDD\Orders\Order_Item $item The current order item.
								 * @param \EDD\Orders\Order $order     The current order object.
								 */
								do_action( 'edd_order_receipt_after_files', $item, $order );
								?>
							</td>
							<?php if ( edd_use_skus() ) : ?>
								<td><?php echo esc_html( edd_get_download_sku( $item->product_id ) ); ?></td>
							<?php endif; ?>
							<?php if ( edd_item_quantities_enabled() ) { ?>
								<td><?php echo esc_html( $item->quantity ); ?></td>
							<?php } ?>
							<td>
								<?php echo esc_html( edd_display_amount( $item->total, $order->currency ) ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>

			</table>

		</div>

	</div>
	
</div>