<!DOCTYPE html>
<html lang="en-US" prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
<head>
    <!-- Metadata -->
    <meta charset="UTF-8">
    <meta name="HandheldFriendly" content="true" />

	<!-- Title -->
	<title><?php _e('Invoice #', 'edd-invoices'); ?><?php echo edd_get_payment_number( $payment->ID ); ?></title>

	<!-- CSS -->
	<link rel="stylesheet" href="<?php echo set_url_scheme( $this->plugin->url . 'css/standard.css', 'relative' ); ?>" type="text/css" media="all" />
</head>

<body class="<?php echo strtolower($status); ?>">

	<div id="main">
		<header id="header">
			<!-- Logo -->
			<div class="alignleft">
				<?php
				if (isset($this->settings['edd-invoices'.'-logo'])) {
					?>
					<img style="max-width: 43px;" src="<?php echo $this->settings['edd-invoices'.'-logo']; ?>" />
					<?php
				}
				?>
			</div>

			<!-- Invoice Details -->
			<div class="alignright">
				<h1><?php printf( __('Invoice %s', 'edd-invoices' ), edd_get_payment_number( $payment->ID ) ); ?></h1>
			</div>
		</header>

		<section id="contacts">
			<div class="alignleft">
				<header style="background-color: #e4f2ff;"><?php printf( __('Invoice %s', 'edd-invoices' ), edd_get_payment_number( $payment->ID ) ); ?></header>

				<article>
					<?php
					// Company Name
					if (isset($this->settings['edd-invoices'.'-company-name'])) {
						?>
						<p><strong><?php echo $this->settings['edd-invoices'.'-company-name']; ?></strong></p>
						<?php
					}

					// Address + Company Details
					$keys = array('address','address2','city','zipcode','country');
					foreach ($keys as $key) {
						if (isset($this->settings['edd-invoices'.'-'.$key])) {
							?>
							<p>
								<?php
								echo $this->settings['edd-invoices'.'-'.$key];
								?>
							</p>
							<?php
						}
					}
					?>
				</article>
			</div>

			<div class="alignright">
				<header style="background-color: #e4f2ff;"><?php _e('Bill To:', 'edd-invoices'); ?></header>

				<article>
					<?php
					if (isset($user['first_name'])) {
						?>
						<p><strong><?php echo $user['first_name']; ?></strong></p>
						<?php
					}
					$keys = array('line1','line2','city','zip','state','country');
					foreach ($keys as $key) {
						if (isset($user['address'][$key])) {
							?>
							<p>
								<?php
								echo $user['address'][$key];
								?>
							</p>
							<?php
						}
					}
					?>
				</article>
			</div>
		</section>

		<!-- Items -->
		<section id="items">
			<header style="background-color: #e4f2ff;"><?php _e('Invoice Items:', 'edd-invoices'); ?></header>

			<table>
				<tfoot>
					<?php
					// Tax
					if (edd_use_taxes()) {
						?>
						<tr>
							<td class="name"><strong><?php _e('Tax', 'edd-invoices'); ?></strong></td>
							<td class="price"><?php echo edd_payment_tax( $payment->ID ); ?></td>
						</tr>
						<?php
					}
					?>

					<!-- Total -->
					<tr>
						<td class="name"><?php _e('Total Price:', 'edd-invoices' ); ?></td>
						<td class="price"><?php echo edd_payment_amount( $payment->ID ); ?></td>
					</tr>

					<!-- Paid -->
					<tr>
						<td class="name"><?php _e('Payment Status:', 'edd-invoices'); ?></td>
						<?php $statuses = edd_get_payment_statuses(); ?>
						<td class="price"><?php echo array_key_exists( $status, $statuses ) ? $statuses[ $status ] : $status; ?></td>
					</tr>
				</tfoot>
				<tbody>
					<?php
					if ($cart) {
						foreach ($cart as $key=>$item) {
							if (empty($item['in_bundle'])) {
								// Single Product
								?>
								<tr>
									<td class="name"><?php echo $item['name']; ?></td>
									<td class="price"><?php echo edd_currency_filter( edd_format_amount( $item[ 'price' ] ), $payment->currency ); ?></td>
								</tr>
								<?php
							}
						}
					}
					$fees = edd_get_payment_fees( $payment->ID );
					if ( $fees ) {
						foreach ( $fees as $key => $fee ) {
							?>
							<tr>
								<td class="name"><?php echo $fee['label']; ?></td>
								<td class="price"><?php echo edd_currency_filter( edd_format_amount( $fee[ 'amount' ] ), $payment->currency ); ?></td>
							</tr>
							<?php
						}
					}
					?>
				</tbody>
			</table>
		</section>

		<!-- Additional Info -->
		<section id="additional-info">
			<div class="alignleft">
				<header style="background-color: #e4f2ff;"><?php _e('Additional Info:', 'edd-invoices'); ?></header>

				<article>
					<!-- Purchase Date -->
					<p>
						<?php
						_e('Purchase Date: ', 'edd-invoices');
						echo empty( $payment->completed_date ) ? date('dS F, Y', strtotime( $payment->date ) ) : date('dS F, Y', strtotime( $payment->completed_date ) );
						?>
					</p>


					<?php
					// Vendor Company Registration #
					if (isset($this->settings['edd-invoices'.'-number']) AND !empty($this->settings['edd-invoices'.'-number'])) {
						?>
						<!-- Vendor Company Registration # -->
						<p>
							<?php
							_e('Vendor Company Registration #: ', 'edd-invoices');
							echo $this->settings['edd-invoices'.'-number'];
							?>
						</p>
						<?php
					}

					// Vendor Tax/VAT #
					if (isset($this->settings['edd-invoices'.'-tax']) AND !empty($this->settings['edd-invoices'.'-tax'])) {
						?>
						<!-- Vendor Tax/VAT # -->
						<p>
							<?php
							_e('Company Tax/VAT #: ', 'edd-invoices');
							echo $this->settings['edd-invoices'.'-tax'];
							?>
						</p>
						<?php
					}
					?>

					<p>&nbsp;</p>

					<?php
					// Customer Tax/VAT #
					if (isset($user['address']['vat']) AND !empty($user['address']['vat'])) {
						?>
						<!-- Customer Tax/VAT # -->
						<p>
							<?php
							_e('Customer Tax/VAT #: ', 'edd-invoices');
							echo $user['address']['vat'];
							?>
						</p>
						<?php
					}

					// Customer Notes
					if (isset($user['address']['notes']) AND !empty($user['address']['notes'])) {
						?>
						<!-- Notes -->
						<p>
							<?php
							echo $user['address']['notes'];
							?>
						</p>
						<?php
					}
					?>
				</article>
			</div>

			<?php
			if (strtolower($status) == 'complete') {
				?>
				<div class="alignright">
					<img src="<?php echo trailingslashit($this->plugin->url); ?>images/paid-sign.png" width="213" height="113" alt="<?php _e('Paid', 'edd-invoices'); ?>" />
				</div>
				<?php
			}
			?>
		</section>
	</div>
</body>
</html>
