/** @jsx jsx */
import { jsx, css } from '@emotion/react'
import { Routes, Route, useNavigate } from 'react-router-dom'
import Account from '../account'
import AccountHome from '../account/home'
import AccountLicenses from '../account/licenses'
import AccountSubscriptions from '../account/subscriptions'
import AccountPurchases from '../account/purchases'
import AccountDownloads from '../account/downloads'
import AccountAffiliate from '../account/affiliate'
import { AccountContext } from '../account/_state/context'
import { useEffect, useContext } from 'react'

function Bootstrap() {
	const [accountState, accountDispatch] = useContext(AccountContext)
	const navigate = useNavigate()

	function getActivePage(pathname) {
		if (pathname === '/') {
			return 'dashboard'
		}

		const params = new Proxy(new URLSearchParams(window.location.search), {
			get: (searchParams, prop) => searchParams.get(prop),
		})

		let accountPageParam = params.accountpage

		if (accountPageParam) {
			navigate('/' + accountPageParam)

			return
		}

		return pathname.substring(1)
	}

	useEffect(() => {
		var activePage = getActivePage(window.location.pathname)

		accountDispatch({
			type: 'SET_ACTIVE_PAGE',
			payload: activePage,
		})
	}, [accountDispatch])

	const AccountInnerCSS = css`
		height: 100vh;
	`

	return (
		<div css={AccountInnerCSS}>
			<Routes>
				<Route
					exact
					path='/'
					element={
						<Account>
							<AccountHome />
						</Account>
					}
				/>

				<Route
					path='/licenses'
					element={
						<Account>
							<AccountLicenses />
						</Account>
					}
				/>

				<Route
					path='/subscriptions'
					element={
						<Account>
							<AccountSubscriptions />
						</Account>
					}
				/>

				<Route
					path='/purchases'
					element={
						<Account>
							<AccountPurchases />
						</Account>
					}
				/>

				<Route
					path='/downloads'
					element={
						<Account>
							<AccountDownloads />
						</Account>
					}
				/>

				<Route
					path='/affiliate'
					element={
						<Account>
							<AccountAffiliate />
						</Account>
					}
				/>

				<Route>{'No route matched!'}</Route>
			</Routes>
		</div>
	)
}

export default Bootstrap
