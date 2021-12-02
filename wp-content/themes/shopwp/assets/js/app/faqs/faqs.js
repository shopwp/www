function toggleAccordian() {
	var $faqQuestion = jQuery('.faq-question')

	$faqQuestion.click(function () {
		var $icon = jQuery(this).find('[data-icon]')

		if ($icon.hasClass('fa-plus-square')) {
			$icon.removeClass('fa-plus-square').addClass('fa-minus-square')
		} else {
			$icon.removeClass('fa-minus-square').addClass('fa-plus-square')
		}

		jQuery(this).next().toggleClass('is-open')
	})
}

function initFAQs() {
	toggleAccordian()
}

export { initFAQs }
