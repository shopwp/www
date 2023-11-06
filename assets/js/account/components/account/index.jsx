/** @jsx jsx */
import { jsx, css } from '@emotion/react'
import Sidebar from './sidebar'
import Body from './body'
import AccountModal from './modal'
import Notice from '../_common/notice'
import { AccountContext } from './_state/context'
const { useEffect, useContext } = wp.element

function Account({ children }) {
	const [accountState, accountDispatch] = useContext(AccountContext)

	function handleWindowSizeChange() {
		accountDispatch({
			type: 'SET_IS_MOBILE',
			payload: window.innerWidth <= 600,
		})
	}
	useEffect(() => {
		window.addEventListener('resize', handleWindowSizeChange)
		return () => {
			window.removeEventListener('resize', handleWindowSizeChange)
		}
	}, [])

	const AppCSS = css`
		display: flex;
		height: 100%;
		align-items: stretch;
		background: #f6f9fc;
	`

	return (
		<div className='App' css={AppCSS}>
			{accountState.isMobile ? (
				<>
					{accountState.customer ? (
						<>
							<Body>
								<Sidebar
									accountState={accountState}
									accountDispatch={accountDispatch}
								/>
								{children}
							</Body>
							<AccountModal />
						</>
					) : null}
				</>
			) : (
				<>
					{accountState.customer ? (
						<>
							<Sidebar
								accountState={accountState}
								accountDispatch={accountDispatch}
							/>
							<Body>{children}</Body>
							<AccountModal />
						</>
					) : null}
				</>
			)}

			{accountState.notice && (
				<Notice global={true} type={accountState.notice.type}>
					{accountState.notice.message}
				</Notice>
			)}
		</div>
	)
}

export default Account
