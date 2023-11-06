/** @jsx jsx */
import { jsx, css } from '@emotion/react'
import AccountBodyHeader from '../body/header'
import { AccountContext } from '../_state/context'
import AccountBodyContent from '../body/content'
import Table from '../../_common/tables'
import Notice from '../../_common/notice'
import { IconExternal } from '../../_common/icons'
import TableBody from '../../_common/tables/body'
import TableHeader from '../../_common/tables/header'
import Th from '../../_common/tables/header/th'
import Td from '../../_common/tables/body/td'
import prettyDate from '../../_common/date'
import { StatusCSS } from '../../_common/styles'
import { ContentLoaderBullet } from '../../_common/content-loaders'

const { useContext } = wp.element

function Subscription({ subscription }) {
	if (subscription.name.includes('WP Shopify')) {
		var subName = 'ShopWP Pro'
	} else if (subscription.name.includes('Beaver')) {
		var subName = 'Beaver Builder Extension'
	} else if (subscription.name.includes('Elementor')) {
		var subName = 'Elementor Extension'
	} else if (subscription.name.includes('Recharge')) {
		var subName = 'Recharge Extension'
	} else {
		var subName = subscription.name
	}

	function prettyGateway(gateway) {
		if (gateway === 'stripe') {
			return 'Credit card'
		}

		return 'PayPal'
	}

	const LastFourCSS = css`
		margin-left: 6px;
		font-size: 87%;
		position: relative;
		top: -1px;
	`

	return (
		<tr>
			<Td>
				<p>{subName}</p>
			</Td>
			<Td>
				${subscription.recurring_amount} / {subscription.period}
			</Td>
			<Td
				extraCSS={StatusCSS(
					subscription.status === 'pending' ? 'active' : subscription.status
				)}>
				{subscription.status === 'pending' ? 'Active' : subscription.status}
			</Td>
			<Td>{prettyDate(subscription.expiration)}</Td>
			<Td>
				{prettyGateway(subscription.gateway)}

				{subscription.card_info && (
					<span css={LastFourCSS}> •••• {subscription.card_info.last4}</span>
				)}
			</Td>

			<Td>
				<SubscriptionActionLinks subscription={subscription} />
			</Td>
		</tr>
	)
}

function SubscriptionActionLinks({ subscription }) {
	const [, accountDispatch] = useContext(AccountContext)

	function openPaymentUpdateModal(e) {
		e.preventDefault()

		accountDispatch({ type: 'SET_ACTIVE_MODAL_VIEW', payload: 'paymentUpdate' })
		accountDispatch({ type: 'SET_ACTIVE_SUBSCRIPTION', payload: subscription })
		accountDispatch({ type: 'TOGGLE_MODAL', payload: true })
	}

	function openSubscriptionCancelModal(e) {
		e.preventDefault()

		accountDispatch({
			type: 'SET_ACTIVE_MODAL_VIEW',
			payload: 'subscriptionCancel',
		})
		accountDispatch({ type: 'SET_ACTIVE_SUBSCRIPTION', payload: subscription })
		accountDispatch({ type: 'TOGGLE_MODAL', payload: true })
	}

	function openSubscriptionReactivateModal(e) {
		e.preventDefault()

		accountDispatch({
			type: 'SET_ACTIVE_MODAL_VIEW',
			payload: 'subscriptionReactivate',
		})
		accountDispatch({ type: 'SET_ACTIVE_SUBSCRIPTION', payload: subscription })
		accountDispatch({ type: 'TOGGLE_MODAL', payload: true })
	}

	const SubscriptionActionCSS = css`
		color: black;
		padding: 4px 0 4px 20px;
		display: inline-block;
		margin-right: 16px;
		position: relative;

		> span {
			position: absolute;
			left: 0;
		}

		&:hover {
			color: #415aff;
		}
	`
	return (
		<div>
			{!subscription.status.includes('cancel') ? (
				<a
					href='!#'
					css={SubscriptionActionCSS}
					onClick={openPaymentUpdateModal}>
					<span>✏️</span>Update payment method
				</a>
			) : null}

			{subscription.status.includes('cancel') ? (
				subscription.gateway.includes('paypal') ||
				subscription.gateway.includes('manual') ? (
					<PurchaseNewSubscription
						actionCSS={SubscriptionActionCSS}
						onClickCallback={openSubscriptionCancelModal}
					/>
				) : (
					<ReactivateSubscription
						actionCSS={SubscriptionActionCSS}
						onClickCallback={openSubscriptionReactivateModal}
					/>
				)
			) : (
				<CancelSubscription
					actionCSS={SubscriptionActionCSS}
					onClickCallback={openSubscriptionCancelModal}
				/>
			)}
		</div>
	)
}

function PurchaseNewSubscription({ actionCSS }) {
	return (
		<a
			href={swp.misc.siteUrl + '/purchase'}
			target='_blank'
			rel='noreferrer'
			css={actionCSS}>
			<span>❇️</span>Purchase new subscription <IconExternal />
		</a>
	)
}

function ReactivateSubscription({ actionCSS, onClickCallback }) {
	return (
		<a href='!#' onClick={onClickCallback} css={actionCSS}>
			<span>🔄</span>Reactivate subscription
		</a>
	)
}

function CancelSubscription({ actionCSS, onClickCallback }) {
	return (
		<a href='!#' onClick={onClickCallback} css={actionCSS}>
			<span>🚫</span>Cancel subscription
		</a>
	)
}

function Subscriptions({ subscriptions }) {
	const SubscriptionsTableCSS = css`
		width: 100%;
		max-width: 100%;
	`

	console.log('subscriptions', subscriptions)

	return (
		<Table extraCSS={SubscriptionsTableCSS}>
			<TableHeader>
				<Th>Subscription</Th>
				<Th>Amount</Th>
				<Th>Status</Th>
				<Th>Renewal Date</Th>
				<Th>Purchase Method</Th>
				<Th>Actions</Th>
			</TableHeader>
			<TableBody>
				{subscriptions.map(subscription =>
					subscription.name ? (
						<Subscription key={subscription.id} subscription={subscription} />
					) : null
				)}
			</TableBody>
		</Table>
	)
}

function AccountSubscriptions() {
	const [accountState] = useContext(AccountContext)

	const purchaseLinkCSS = css`
		margin-left: 8px;
	`

	return (
		<>
			<AccountBodyHeader
				heading='Subscriptions'
				totalItems={accountState.subscriptions.length}
			/>

			<AccountBodyContent>
				{accountState.customer ? (
					accountState.subscriptions.length ? (
						<Subscriptions subscriptions={accountState.subscriptions} />
					) : (
						<Notice type='info'>
							No subscriptions found!
							<a
								href={swp.misc.siteUrl + '/purchase'}
								target='_blank'
								rel='noreferrer'
								css={purchaseLinkCSS}>
								Purchase one today.
							</a>
						</Notice>
					)
				) : (
					<ContentLoaderBullet />
				)}
			</AccountBodyContent>
		</>
	)
}

export default AccountSubscriptions
