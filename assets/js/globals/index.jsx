import tippy from 'tippy.js'

function toggleIsSelected($element, state) {
	if (state) {
		$element.attr('data-is-selected', 'true')
	} else {
		$element.removeAttr('data-is-selected')
	}
}

function updateSelectedDemo(selectedScreen, $screens) {
	$screens.each((index, el) => toggleIsSelected(jQuery(el), false))

	$screens.each((index, el) => {
		toggleIsSelected(jQuery(el), false)

		let sc = jQuery(el).attr('data-screen')

		if (sc === selectedScreen) {
			toggleIsSelected(jQuery(el), true)
		}
	})
}

function initScreenshots() {
	var hash = window.location.hash.split('#')

	if (hash.length) {
		hash = hash[1]

		if (!hash || hash === '' || hash === '!') {
			hash = 'storefront'
		}
	} else {
		hash = 'storefront'
	}

	var $screens = jQuery('.nav-screens.component-features-demo .screen')
	updateSelectedDemo(hash, $screens)

	// jQuery('#demo-selector').value = hash

	jQuery('#demo-selector option[value="' + hash + '"]').attr(
		'selected',
		'selected'
	)

	jQuery('#demo-selector').change('change', function (stuff) {
		var selectedScreen = stuff.target.value

		window.location.hash = '#' + stuff.target.value

		updateSelectedDemo(selectedScreen, $screens)
	})

	jQuery('.nav-equal .nav-link').on('click', function (stuff) {
		var screenVal = jQuery(this).data('screen')
		var $screens = jQuery('.component-screenshots .screen')
		var $screensLinks = jQuery('.component-screenshots .nav-link')

		updateSelectedDemo(screenVal, $screensLinks)
		updateSelectedDemo(screenVal, $screens)
	})
}

function initThemeToggle() {
	var themeButtons = document.querySelectorAll('.theme-toggle')
	var htmlEl = document.querySelectorAll('html')

	themeButtons.forEach(element => {
		element.addEventListener(
			'click',
			function (event) {
				let theme = element.getAttribute('data-theme')

				var date = new Date()
				date.setTime(date.getTime() + 365 * 24 * 60 * 60 * 1000)

				var expires = '; expires=' + date.toGMTString()

				htmlEl.classList = 'theme-' + theme

				jQuery('html').removeClass(['theme-dark', 'theme-light'])
				jQuery('html').addClass('theme-' + theme)

				document.cookie = 'shopwp_theme=' + theme + ';' + expires + '; path=/;'
			},
			false
		)
	})
}

function initTooltips() {
	tippy('.chart-label, .tooltip-label', {
		content: function (reference) {
			const content = jQuery(reference).find('.tooltip-label-description')
			return content[0].innerHTML
		},
		interactive: true,
		trigger: 'mouseenter',
		animation: 'shift-toward',
		theme: 'dark',
		arrow: true,
		arrowType: 'round',
		distance: 7,
		placement: 'right',
		maxWidth: 380,
		duration: [280, 0],
		moveTransition: 'transform 0.2s ease-out',
		offset: [0, -80],
		allowHTML: true,
		appendTo: function (reference) {
			const labelP = jQuery(reference).find('> p')
			return jQuery(reference)[0]
		},
	})
}

function initFAQs() {
	var $faqQuestion = jQuery('.faq-question')

	$faqQuestion.click(function () {
		var $iconMinus = jQuery(this).find('.faq-icon.is-hiding')
		var $iconPlus = jQuery(this).find('.faq-icon.is-showing')

		$iconMinus.removeClass('is-hiding').addClass('is-showing')
		$iconPlus.removeClass('is-showing').addClass('is-hiding')

		jQuery(this).toggleClass('is-open')
	})
}

function initSubNav() {
	var $parent = jQuery('.nav-link-parent')

	$parent.hover(function () {
		var $parent = jQuery(this)

		$parent.toggleClass('is-showing')

		$parent.next('.sub-nav').hover(
			function () {
				$parent.addClass('is-showing')
			},
			function () {
				$parent.removeClass('is-showing')
			}
		)
	})
}

function initMarqueeDemo() {
	jQuery('.btn-demo-click').on('click', function (e) {
		e.preventDefault()

		jQuery('.marquee-demo-inner').addClass('anime-grow')
		setTimeout(function () {
			jQuery('.marquee-demo-inner').removeClass('anime-grow')
		}, 800)
	})
}

function initMobileMenu() {
	jQuery('.icon-mobile').on('click', function (e) {
		e.preventDefault()
		jQuery('body').toggleClass('is-showing-mobile-menu')
	})
}

function initGlobals() {
	initTooltips()
	initThemeToggle()
	initScreenshots()
	initFAQs()
	initSubNav()
	initMarqueeDemo()
	initMobileMenu()
}

export default initGlobals
