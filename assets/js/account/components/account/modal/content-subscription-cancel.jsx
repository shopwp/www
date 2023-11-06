/** @jsx jsx */
import { jsx, css } from '@emotion/react'
import ModalHeader from './header'
import ModalBody from './body'
import Button from '../../_common/button'
import { cancelSubscription } from '../../_common/api'
import Notice from '../../_common/notice'
import to from 'await-to-js'
const { useState } = wp.element

function ModalContentSubscriptionCancel({ accountState, accountDispatch }) {
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

		ul {
			margin-top: 5px;
			margin-bottom: -10px;
		}
	`

	async function onCancel() {
		setIsBusy(true)

		const [error, resp] = await to(
			cancelSubscription({
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

		resp.data = resp.data.map(sub => {
			if (sub.id === accountState.subscription.id) {
				sub.status = 'canceled'
			}

			return sub
		})

		accountDispatch({
			type: 'SET_SUBSCRIPTIONS',
			payload: resp.data,
		})

		accountDispatch({
			type: 'SET_NOTICE',
			payload: {
				message: 'Successfully canceled subscription',
				type: 'success',
			},
		})

		setTimeout(() => {
			accountDispatch({
				type: 'SET_NOTICE',
				payload: false,
			})
		}, 5500)
	}

	return (
		<div>
			<ModalHeader
				text={
					'Cancel subscription to: ' +
					accountState.subscription.name.replace('WP Shopify', 'ShopWP Pro')
				}
			/>
			<ModalBody>
				<Notice type='warning'>
					Are you sure you want to cancel your subscription?
				</Notice>
				<p css={altPCSS}>
					<strong>What will happen:</strong>
					<ul>
						<li>
							Your subscription will end immediately and you will no longer be
							charged
						</li>
						<li>Your ShopWP license key will be deactivated</li>
						<li>
							You will lose access to any future ShopWP updates and product
							support
						</li>
					</ul>
				</p>
				{accountState.subscription.gateway.includes('paypal') ? (
					<small css={smallCSS}>
						This cannot be reversed. You will need to purchase a new
						subscription if you wish to continue receiving plugin updates and
						support. Email us if you have any questions:{' '}
						<a href='mailto:hello@wpshop.io' rel='noreferrer' target='_blank'>
							hello@wpshop.io
						</a>
					</small>
				) : null}

				<Button
					size='small'
					text='Yes, cancel subscription now'
					onClick={onCancel}
					disabled={isBusy}
					loadingText='Canceling ...'
				/>
			</ModalBody>
		</div>
	)
}

export default ModalContentSubscriptionCancel
