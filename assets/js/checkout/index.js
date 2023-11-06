jQuery(document).on(
	'click',
	'#edd_purchase_form #edd_purchase_submit [type=submit]',
	function (e) {
		const eddPurchaseform = document.getElementById('edd_purchase_form')

		if (
			!eddPurchaseform ||
			eddPurchaseform.length === 0 ||
			typeof eddPurchaseform.checkValidity !== 'function'
		) {
			return
		}

		if (!eddPurchaseform.checkValidity()) {
			return
		}

		jQuery('#edd_checkout_form_wrap').addClass('swp-is-loading')
	}
)

jQuery(document.body).on('edd_checkout_error', function (stuff) {
	jQuery('#edd_checkout_form_wrap').removeClass('swp-is-loading')
})
