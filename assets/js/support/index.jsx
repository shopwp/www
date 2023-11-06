import { useForm } from 'react-hook-form'

function SupportLicenseKeys({
	values,
	setValue,
	fieldName,
	type = 'license keys',
}) {
	return (
		<div className='support-keys'>
			<p className='support-keys-label'>
				Hey {swp.user.first} 👋, here are your ShopWP {type}:{' '}
				<small>(Click to add)</small>
			</p>
			{values.map(obj => (
				<p
					key={obj.value}
					className='l-col l-align-items-flex-start support-key'
					onClick={() =>
						setValue(
							fieldName,
							type === 'websites' ? 'https://' + obj.value : obj.value,
							{
								shouldValidate: true,
								shouldTouch: true,
							}
						)
					}>
					{obj?.name ? (
						<span className='support-key-name'>
							{obj.name.replace('WP Shopify', 'ShopWP Pro')}:
						</span>
					) : null}

					<span className='support-key-value'>{obj.value}</span>
				</p>
			))}
		</div>
	)
}
function SupportForm() {
	const { useState, useRef, useEffect } = wp.element
	const [selectedFiles, setSelectedFiles] = useState(null)
	const [error, setError] = useState(false)
	const [success, setSuccess] = useState(null)
	const [isBusy, setIsBusy] = useState(false)

	const [licenseKeys] = useState(() => {
		if (swp.misc.isLoggedIn == false) {
			return false
		}
		return swp.user.licenseKeys.map(keyObj => {
			return { name: keyObj.name, value: keyObj.key }
		})
	})

	const [websites] = useState(() => {
		var sites = []

		if (swp.misc.isLoggedIn == false) {
			return false
		}

		swp.user.licenseKeys.forEach(keyObj => {
			keyObj.sites.forEach(site =>
				sites.push({ value: site.url.replace(/^\/|\/$/g, '') })
			)
		})

		return sites
	})

	const formElement = useRef(null)

	const {
		register,
		handleSubmit,
		setValue,
		formState: { errors },
		reset,
		watch,
	} = useForm({
		defaultValues: {
			firstName: swp.user ? swp.user.first : '',
			lastName: swp.user ? swp.user.last : '',
			email: swp.user ? swp.user.email : '',
		},
	})

	const watchTopic = watch('topic', 'Pre-sale')

	useEffect(() => {
		if (success === null || success === false) {
			return
		}

		reset()
		setSelectedFiles(null)
	}, [success])

	async function createTicket(url, data) {
		setIsBusy(true)
		setError(false)
		setSuccess(false)

		try {
			var resp = await fetch(url, {
				method: 'POST',
				headers: {
					'X-WP-Nonce': swp.api.nonce,
				},
				body: data,
			})
				.then(response => {
					return response.json()
				})
				.then(resp => resp)
				.catch(error => {
					console.log('ShopWP Create Ticket Error', error)
					return error
				})

			setIsBusy(false)

			if (!resp.success) {
				setError(resp.data)
			} else {
				setSuccess(resp.data)
			}
		} catch (error) {
			setIsBusy(false)
		}
	}

	function onSubmit(data) {
		const url = formElement.current.action

		const formData = new FormData()

		for (const key in data) {
			if (key === 'files') {
				if (data[key].length) {
					data[key].forEach(file => {
						formData.append('files[]', file)
					})
				}
			} else {
				formData.append(key, data[key])
			}
		}

		createTicket(url, formData)
	}

	function onFileSelect(e) {
		if (isBusy) {
			return
		}

		var newFiles = Array.from(e.target.files)

		setSelectedFiles(newFiles)
		setValue('files', newFiles, { shouldTouch: true })
	}

	function formatBytes(bytes, decimalPoint) {
		if (bytes == 0) return '0 Bytes'
		var k = 1000,
			dm = decimalPoint || 2,
			sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
			i = Math.floor(Math.log(bytes) / Math.log(k))
		return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i]
	}

	return (
		<>
			{!success ? (
				<form
					onSubmit={handleSubmit(onSubmit)}
					className='l-col l-align-items-flex-start'
					action='/wp-json/swp/v8/ticket/create'
					method='post'
					enctype='multipart/form-data'
					ref={formElement}
					data-is-busy={isBusy}
					autocomplete>
					<div className='l-row l-sb l-fill'>
						<label className='l-box-2'>
							<div className='label-text'>
								First name <span className='required-icon'>*</span>
							</div>
							<input
								{...register('firstName', { required: true })}
								disabled={isBusy}
								placeholder='First name'
							/>
							{errors.firstName && (
								<p className='form-error'>First name is required.</p>
							)}
						</label>
						<label className='l-box-2'>
							<div className='label-text'>Last name</div>
							<input
								{...register('lastName')}
								placeholder='Last name'
								disabled={isBusy}
							/>
						</label>
					</div>

					<label className='l-fill'>
						<div className='label-text'>
							Email <span className='required-icon'>*</span>
						</div>
						<input
							{...register('email', { required: true })}
							placeholder='Email'
							disabled={isBusy}
						/>
						{errors.email && <p className='form-error'>Email is required.</p>}
					</label>

					<label className='l-fill'>
						<div className='label-text'>Website</div>
						<input
							{...register('website')}
							type='url'
							placeholder='Your WordPress URL'
							disabled={isBusy}
						/>
						<div className='support-description'>
							Sharing your site URL can help us debug the issue more quickly.
						</div>
						{swp.misc.isLoggedIn ? (
							<SupportLicenseKeys
								values={websites}
								setValue={setValue}
								fieldName='website'
								type='websites'
							/>
						) : null}
					</label>

					<label className='l-fill'>
						<div className='label-text'>
							Topic <span className='required-icon'>*</span>
						</div>
						<select
							{...register('topic', {
								required: true,
							})}
							disabled={isBusy}>
							<option value='Pre-sale'>Pre-sale question</option>
							<option value='Technical'>Technical question</option>
							<option value='General'>General question</option>
							<option value='Account / Billing'>
								Account / Billing question
							</option>
						</select>
						{errors.license && (
							<p className='form-error'>Please choose a support topic.</p>
						)}
					</label>

					{watchTopic === 'Technical' || watchTopic === 'Account / Billing' ? (
						<label className='l-fill'>
							<div className='label-text'>
								ShopWP License Key{' '}
								{watchTopic === 'Technical' ||
								watchTopic === 'Account / Billing' ? (
									<span className='required-icon'>*</span>
								) : null}
							</div>
							<input
								{...register('license', {
									pattern: /^[A-Za-z0-9]*$/,
									required: watchTopic === 'Technical' ? true : false,
								})}
								disabled={isBusy}
							/>
							{errors.license && (
								<p className='form-error'>
									Please enter a valid ShopWP license key.
								</p>
							)}
							<div className='support-description'>
								**Important** If you need technical support, you must provide an
								active ShopWP license key here.{' '}
								<a href='/purchase'>Purchase one here</a>.
							</div>

							{swp.misc.isLoggedIn ? (
								<SupportLicenseKeys
									values={licenseKeys}
									setValue={setValue}
									fieldName='license'
								/>
							) : null}
						</label>
					) : null}

					<label className='l-fill'>
						<div className='label-text'>
							Question(s) / Message <span className='required-icon'>*</span>
						</div>
						<textarea
							{...register('notes', { required: true })}
							disabled={isBusy}
							dir='auto'
						/>
						<div className='support-description'>
							Please add your main question(s) here. Please add any additional
							notes that you think is relevant. The more information you provide
							the better we can help!
						</div>
						{errors.notes && (
							<p className='form-error'>Please add your question(s).</p>
						)}
					</label>

					{watchTopic === 'Technical' ? (
						<label className='l-fill form-file'>
							<div className='label-text'>
								System Info{' '}
								{watchTopic === 'Technical' ? (
									<span className='required-icon'>*</span>
								) : null}
							</div>
							<textarea
								{...register('systemInfo', {
									required: watchTopic === 'Technical' ? true : false,
								})}
								placeholder='Dashboard → Tools → Site Health → Info → Copy'
								disabled={isBusy}
								dir='auto'
							/>
							<div className='support-description'>
								Copy and paste your system info here. You can find this by
								opening WordPress and going to: Dashboard &rarr; Tools &rarr;
								Site Health &rarr; Info &rarr; Copy site info to clipboard
							</div>
							{errors.systemInfo && (
								<p className='form-error'>Please add your system info.</p>
							)}
						</label>
					) : null}

					{watchTopic === 'Technical' || watchTopic === 'General' ? (
						<label className='l-fill form-file'>
							<div className='label-text'>Upload Additional Files</div>
							<input
								type='file'
								{...register('files')}
								onChange={onFileSelect}
								multiple
								disabled={isBusy}
							/>
							<div class='form-file-dropzone'>
								{selectedFiles ? (
									<ul>
										{selectedFiles.map(file => (
											<li>
												{file.name} &ndash; {formatBytes(file.size)}
											</li>
										))}
									</ul>
								) : (
									<>
										<svg
											xmlns='http://www.w3.org/2000/svg'
											viewBox='0 0 512 512'>
											<path d='M121 32C91.6 32 66 52 58.9 80.5L1.9 308.4C.6 313.5 0 318.7 0 323.9V416c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V323.9c0-5.2-.6-10.4-1.9-15.5l-57-227.9C446 52 420.4 32 391 32H121zm0 64H391l48 192H387.8c-12.1 0-23.2 6.8-28.6 17.7l-14.3 28.6c-5.4 10.8-16.5 17.7-28.6 17.7H195.8c-12.1 0-23.2-6.8-28.6-17.7l-14.3-28.6c-5.4-10.8-16.5-17.7-28.6-17.7H73L121 96z' />
										</svg>
										<p>Click or drag a file to this area to upload.</p>
									</>
								)}
							</div>
							<div className='support-description'>
								Upload any screenshots, error logs, or other files to help us
								solve your issue.
							</div>
						</label>
					) : null}

					<div className='l-row'>
						<input
							type='submit'
							className='btn btn-l'
							disabled={isBusy}
							value={isBusy ? 'Sending, please wait ...' : 'Submit ticket'}
						/>
						{isBusy ? (
							<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'>
								<path d='M304 48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zm0 416a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM48 304a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm464-48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM142.9 437A48 48 0 1 0 75 369.1 48 48 0 1 0 142.9 437zm0-294.2A48 48 0 1 0 75 75a48 48 0 1 0 67.9 67.9zM369.1 437A48 48 0 1 0 437 369.1 48 48 0 1 0 369.1 437z' />
							</svg>
						) : null}
					</div>
					{error ? <p className='form-error'>{error}</p> : null}
				</form>
			) : null}

			{success ? (
				<p className='form-success notice notice-success'>
					<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'>
						<path d='M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z' />
					</svg>
					{success}{' '}
				</p>
			) : null}
		</>
	)
}

function initSupportForm() {
	var supportRootEl = null

	document.addEventListener('DOMContentLoaded', function (event) {
		if (!supportRootEl) {
			supportRootEl = document.getElementById('root-support')

			if (supportRootEl) {
				var realRoot = wp.element.createRoot(supportRootEl)

				realRoot.render(<SupportForm />)
			}
		}
	})
}

export default initSupportForm
