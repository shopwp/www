function request(data) {
	return new Promise((resolve, reject) => {
		fetch(swp.api.restUrl + swp.api.namespace + '/contact/add', {
			method: 'POST',
			headers: {
				'X-WP-Nonce': swp.api.nonce,
				'Content-Type': 'application/json',
			},
			body: JSON.stringify(data),
		})
			.then(response => {
				return response.json()
			})
			.then(resp => {
				resolve(resp)
			})
			.catch(error => {
				console.log('ShopWP Request Error:', error)
				reject(error)
			})
	})
}
function Newsletter() {
	const [isBusy, setIsBusy] = wp.element.useState(false)
	const [notice, setNotice] = wp.element.useState(false)
	const emailEl = wp.element.useRef(null)

	async function addContacts(email) {
		setIsBusy(true)
		setNotice(false)

		try {
			const resp = await request({ email: email })

			if (resp.success) {
				setNotice({
					type: 'success',
					message: resp.data,
				})
				emailEl.current.value = ''
			} else {
				console.error('ShopWP Error: ', resp.data)
				setNotice({
					type: 'error',
					message: resp.data,
				})

				emailEl.current.focus()
			}
		} catch (error) {
			console.error('ShopWP Error: ', JSON.stringify(error))
			setNotice({
				type: 'error',
				message: JSON.stringify(error),
			})

			emailEl.current.focus()
		}

		setIsBusy(false)
	}

	function onSubmit(event) {
		event.preventDefault()

		addContacts(event.target[0].value)
	}

	return (
		<>
			<form className='l-row newsletter-wrapper' onSubmit={onSubmit}>
				<input
					className='l-br'
					type='email'
					placeholder='Enter your email'
					onFocus={e => e.target.select()}
					disabled={isBusy}
					ref={emailEl}
				/>
				<button class='btn btn-secondary link' type='submit' disabled={isBusy}>
					{isBusy ? (
						<svg
							xmlns='http://www.w3.org/2000/svg'
							viewBox='0 0 512 512'
							className='anime anime-spin'>
							<path d='M304 48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zm0 416a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM48 304a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm464-48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM142.9 437A48 48 0 1 0 75 369.1 48 48 0 1 0 142.9 437zm0-294.2A48 48 0 1 0 75 75a48 48 0 1 0 67.9 67.9zM369.1 437A48 48 0 1 0 437 369.1 48 48 0 1 0 369.1 437z' />
						</svg>
					) : (
						'Sign up'
					)}
				</button>
			</form>
			{notice ? (
				<p className={'notice notice-' + notice.type}>
					{notice.type === 'success' ? (
						<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'>
							<path d='M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z' />
						</svg>
					) : null}

					{notice.message}
				</p>
			) : null}
		</>
	)
}

function initNewsletter() {
	var newsletterFooterEl = null
	var newsletterPostsEl = null

	document.addEventListener('DOMContentLoaded', function (event) {
		if (!newsletterFooterEl) {
			newsletterFooterEl = document.getElementById('root-newsletter-footer')

			if (newsletterFooterEl) {
				var realRoot = wp.element.createRoot(newsletterFooterEl)

				realRoot.render(<Newsletter />)
			}
		}

		if (!newsletterPostsEl) {
			newsletterPostsEl = document.getElementById('root-newsletter-posts')

			if (newsletterPostsEl) {
				var realRoot = wp.element.createRoot(newsletterPostsEl)

				realRoot.render(<Newsletter />)
			}
		}
	})
}

export default initNewsletter
