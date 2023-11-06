function get(endpoint) {
	return request('get', swp.api.restUrl + swp.api.namespace + '/' + endpoint)
}

function post(endpoint, data = {}, formData = false) {
	return request(
		'post',
		swp.api.restUrl + swp.api.namespace + '/' + endpoint,
		data,
		formData
	)
}

function del(endpoint, data = {}) {
	return request(
		'delete',
		swp.api.restUrl + swp.api.namespace + '/' + endpoint,
		data
	)
}

function request(method, endpoint, data = false, formData = false) {
	return new Promise((resolve, reject) => {
		let options = {
			method: method,
			headers: {
				'Content-Type': 'multipart/form-data',
			},
		}

		if (method !== 'get' && !formData) {
			options.body = JSON.stringify(data)
		} else {
			options.body = data
		}

		options.headers['X-WP-Nonce'] = swp.api.nonce

		fetch(endpoint, options)
			.then(response => {
				return resolve(response.json())
			})
			.catch(error => {
				reject(error)
			})
	})
}

export { get, post, del }
