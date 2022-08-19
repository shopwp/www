/** @jsx jsx */
import { jsx, css } from '@emotion/react'
import Sidebar from './sidebar'
import Body from './body'
import AccountModal from './modal'
import Notice from '../_common/notice'
import { getCustomer } from '../_common/api'
import { useEffect, useContext } from 'react'
import { AccountContext } from './_state/context'
import to from 'await-to-js'

function Account({ children }) {
	const [accountState, accountDispatch] = useContext(AccountContext)

	const AppCSS = css`
		display: flex;
		height: 100%;
		align-items: stretch;
		background: #f6f9fc;
	`

	async function getCust() {
		const [error, customer] = await to(getCustomer())

		if (customer.code && customer.code === 'internal_server_error') {
			return
		}

		accountDispatch({ type: 'SET_CUSTOMER', payload: customer })
		accountDispatch({
			type: 'SET_SUBSCRIPTIONS',
			payload: customer.subscriptions,
		})
	}

	useEffect(() => {
		getCust()
	}, [])

	return (
		<div className='App' css={AppCSS}>
			<Sidebar />
			<Body>{children}</Body>
			<AccountModal />

			{accountState.notice && (
				<Notice global={true} type={accountState.notice.type}>
					{accountState.notice.message}
				</Notice>
			)}
		</div>
	)
}

export default Account
