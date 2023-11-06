function getApiRestURL() {
	return swp.api.restUrl
}

async function deactivateLicense(data) {
	const apiURL = getApiRestURL()

	const response = await fetch(apiURL + 'customers/v1/deactivate_license', {
		body: JSON.stringify({
			key: data.key,
			item_name: data.itemName,
			item_id: data.itemId,
			siteURL: data.url,
			apiURL: apiURL,
			email: data.email,
		}),
		method: 'post',
		headers: {
			Accept: 'application/json',
			'Content-Type': 'application/json',
			'X-WP-Nonce': swp.api.nonce,
		},
	})

	return await response.json()
}

async function updateProfile(data) {
	const apiURL = getApiRestURL()

	const response = await fetch(apiURL + 'customers/v1/update/profile', {
		body: JSON.stringify(data),
		method: 'post',
		headers: {
			Accept: 'application/json',
			'Content-Type': 'application/json',
			'X-WP-Nonce': swp.api.nonce,
		},
	})

	return await response.json()
}

async function cancelSubscription(data) {
	const apiURL = getApiRestURL()

	const response = await fetch(apiURL + 'customers/v1/subscription/cancel', {
		body: JSON.stringify(data),
		method: 'post',
		headers: {
			Accept: 'application/json',
			'Content-Type': 'application/json',
			'X-WP-Nonce': swp.api.nonce,
		},
	})

	return await response.json()
}

async function reactivateSubscription(data) {
	const apiURL = getApiRestURL()
	var saoks = apiURL + 'customers/v1/subscription/reactivate'

	const response = await fetch(saoks, {
		body: JSON.stringify(data),
		method: 'post',
		headers: {
			Accept: 'application/json',
			'Content-Type': 'application/json',
			'X-WP-Nonce': swp.api.nonce,
		},
	})

	return await response.json()
}

async function updatePaymentMethod(data) {
	const apiURL = getApiRestURL()

	const response = await fetch(apiURL + 'customers/v1/update/payment', {
		body: JSON.stringify(data),
		method: 'post',
		headers: {
			Accept: 'application/json',
			'Content-Type': 'application/json',
			'X-WP-Nonce': swp.api.nonce,
		},
	})

	return await response.json()
}

async function logoutUser(data) {
	const apiURL = getApiRestURL()

	const response = await fetch(apiURL + 'customers/v1/logout', {
		body: JSON.stringify(data),
		method: 'post',
		headers: {
			Accept: 'application/json',
			'Content-Type': 'application/json',
			'X-WP-Nonce': swp.api.nonce,
		},
	})

	return await response.json()
}

async function getCustomer() {
	const apiURL = getApiRestURL()

	const response = await fetch(apiURL + 'customers/v1/get', {
		method: 'post',
		headers: {
			Accept: 'application/json',
			'Content-Type': 'application/json',
			'X-WP-Nonce': swp.api.nonce,
		},
	})

	return await response.json()
}

export {
	updateProfile,
	updatePaymentMethod,
	deactivateLicense,
	cancelSubscription,
	reactivateSubscription,
	logoutUser,
	getApiRestURL,
	getCustomer,
}
