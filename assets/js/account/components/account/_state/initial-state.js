function AccountInitialState() {
	return {
		customer: false,
		subscription: false,
		subscriptions: false,
		isModalOpen: false,
		activeModalView: false,
		activePage: 'dashboard',
		isMobile: window.innerWidth <= 600,
		isMobileMenuOpen: false,
		pages: [
			{
				title: 'dashboard',
				link: '/',
			},
			{
				title: 'licenses',
				link: '/licenses',
			},
			{
				title: 'subscriptions',
				link: '/subscriptions',
			},
			{
				title: 'purchases',
				link: '/purchases',
			},
			{
				title: 'downloads',
				link: '/downloads',
			},
			{
				title: 'affiliate',
				link: '/affiliate',
			},
		],
		notice: false,
	}
}

export { AccountInitialState }
