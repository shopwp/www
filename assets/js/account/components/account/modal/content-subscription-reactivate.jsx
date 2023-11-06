/** @jsx jsx */
import { jsx, css } from '@emotion/react'
import ModalHeader from './header'
import ModalBody from './body'
import Button from '../../_common/button'
import { reactivateSubscription } from '../../_common/api'
import Notice from '../../_common/notice'
import to from 'await-to-js'
const { useState } = wp.element

function ModalContentSubscriptionReactivate({ accountState, accountDispatch }) {
	const [isBusy, setIsBusy] = useState(false)

	const smallCSS = css`
		display: block;
		margin-bottom: 15px;
		line-height: 1.5;
	`

	const altPCSS = css`
		font-size: 15px;
		display: block;
		margin-bottom: 15px;

		strong {
			font-family: Metropolis;
			font-weight: 500;
		}
	`

	async function onReactivate() {
		setIsBusy(true)

		const [error, resp] = await to(
			reactivateSubscription({
				subscription: accountState.subscription,
				email: accountState.customer.info.email,
			})
		)

		setIsBusy(false)
		accountDispatch({ type: 'TOGGLE_MODAL', payload: false })

		if (error) {
			accountDispatch({
				type: 'SET_NOTICE',
				payload: {
					message: JSON.stringify(error),
					type: 'error',
				},
			})

			return
		}

		if (!resp.success) {
			accountDispatch({
				type: 'SET_NOTICE',
				payload: {
					message: resp.data,
					type: 'error',
				},
			})

			return
		}

		accountDispatch({
			type: 'SET_NOTICE',
			payload: {
				message: 'Successfully reactivated subscription',
				type: 'success',
			},
		})

		accountDispatch({
			type: 'SET_SUBSCRIPTIONS',
			payload: resp.data,
		})

		setTimeout(() => {
			accountDispatch({
				type: 'SET_NOTICE',
				payload: false,
			})
		}, 5500)
	}

	function createRenewalDate(dateString) {
		const aYearFromNow = new Date(dateString)
		aYearFromNow.setFullYear(aYearFromNow.getFullYear() + 1)
		return aYearFromNow.toLocaleDateString('en-US', {
			weekday: 'long',
			year: 'numeric',
			month: 'long',
			day: 'numeric',
		})
	}

	console.log('accountState.subscription.name', accountState.subscription.name)
	return (
		<div>
			<ModalHeader text='Reactivate subscription' />
			<ModalBody>
				<Notice type='warning'>
					Do you you want to reactivate your subscription?
				</Notice>
				<p css={altPCSS}>
					Reactivating subscrption to:{' '}
					<strong>
						{accountState.subscription.name.replace('WP Shopify', 'ShopWP Pro')}
					</strong>
				</p>
				<small css={smallCSS}>
					<>
						{'You will not be charged now. You will continue on your previous payment schedule and your next charge will be on: ' +
							createRenewalDate(accountState.subscription.created)}
						{'. '}

						<p>
							Please email us if you have any questions:{' '}
							<a href='mailto:hello@wpshop.io' rel='noreferrer' target='_blank'>
								hello@wpshop.io
							</a>
						</p>
					</>
				</small>
				<Button
					size='small'
					text='Yes, reactivate subscription'
					onClick={onReactivate}
					disabled={isBusy}
					loadingText='Reactivating ...'
				/>
			</ModalBody>
		</div>
	)
}

export default ModalContentSubscriptionReactivate
