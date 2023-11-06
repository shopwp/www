/** @jsx jsx */
import { jsx, css } from '@emotion/react'
import Logo from '../../_common/logo'
import iconStacked from '../../_common/icons/stack.svg'
import Nav from './nav'

function Sidebar({ accountState, accountDispatch }) {
	const NavCSS = css`
		display: flex;
		width: 240px;
		align-items: flex-start;
		background: white;
		flex-direction: column;
		border-right: 1px solid #e3e8ee;
		padding-top: 10px;

		@media (max-width: 600px) {
			position: absolute;
			top: 0;
			width: 100%;
			display: flex;
			flex-direction: row;
			min-height: 70px;
			width: 100%;
			max-width: 100%;
			align-items: center;
			justify-content: space-between;
			position: static;
			padding: 0 10px;
			box-sizing: border-box;
			background: transparent;
			border: none;
			padding: 0;
		}

		> img {
			max-width: 188px;
			margin: 0 auto;
			position: relative;
			left: -9px;
		}
	`
	function onClick() {
		if (accountState.isMobile) {
			accountDispatch({
				type: 'SET_IS_MOBILE_MENU_OPEN',
				payload: !accountState.isMobileMenuOpen,
			})
		}
	}
	return (
		<nav css={NavCSS}>
			<Logo color='#415aff' width='40px' height='40px' />
			<Nav isOpen={accountState.isMobileMenuOpen} />
			{accountState.isMobile ? (
				<MobileIcon onClick={onClick} isOpen={accountState.isMobileMenuOpen} />
			) : null}
		</nav>
	)
}

function MobileIcon({ isOpen, onClick }) {
	const MobileCSS = css`
		position: absolute;
		right: 10px;
		top: 10px;

		svg {
			width: 40px;
			height: 40px;
		}
	`

	return (
		<div css={MobileCSS} onClick={onClick}>
			{isOpen ? (
				<svg
					xmlns='http://www.w3.org/2000/svg'
					xmlSpace='preserve'
					style={{ 'enable-background': 'new 0 0 200 200' }}
					viewBox='0 0 200 200'>
					<path d='m110.6 105 68.6-68.6c1.2-1.2 2.4-2.3 3.4-3.7 2.7-4 1.9-8.4-2-11.3-3.8-2.8-7.5-2.1-12.1 2.5-23.2 23.2-46.4 46.3-69.5 69.5-22.9-22.9-45.7-45.8-68.6-68.6-1.6-1.5-3.3-3-5.2-4.1-3.2-1.7-6.2-.8-8.7 1.8-2.4 2.5-3.6 5.5-1.8 8.7 1.1 2 2.7 3.9 4.4 5.5C41.8 59.5 64.6 82.2 87.4 105c-22.7 22.7-45.5 45.5-68.2 68.3-1.5 1.6-3 3.3-4.1 5.2-1.8 3.2-.8 6.2 1.7 8.7 2.5 2.4 5.5 3.6 8.7 1.8 2-1.1 3.9-2.7 5.5-4.4 22.7-22.6 45.3-45.3 68-67.9l68.2 68.2c1.2 1.2 2.3 2.4 3.7 3.4 4 2.7 8.4 1.9 11.3-2 2.8-3.8 2.1-7.5-2.5-12.1-23-23.1-46-46.2-69.1-69.2z'></path>
				</svg>
			) : (
				<svg
					style={{ 'enable-background': 'new 0 0 200 200' }}
					viewBox='0 0 200 200'>
					<path d='M100.1 21h89.5c5.5 0 8.1 1.8 8.7 5.7.6 4-1.5 7.2-5.5 8-1.4.3-2.8.2-4.2.2H11.8c-2 0-4-.2-5.9-.7-3-.9-4.1-3.4-4.1-6.3-.1-3 1.1-5.3 4.1-6.2 1.8-.5 3.7-.7 5.5-.7h88.7zm0 84.4H11.3c-2 0-4-.2-5.9-.8-2.8-.9-3.7-3.4-3.8-6.2-.1-2.8 1.1-5.1 3.8-6.1 1.7-.6 3.7-.8 5.5-.8h177.9c1.3 0 2.6 0 3.8.3 3.9.8 5.7 3.1 5.6 6.9-.1 3.5-2.3 6-6 6.6-1.3.2-2.6.1-3.9.1h-88.2zm0 70.5H11.3c-2 0-4-.2-5.9-.8-2.8-.9-3.7-3.4-3.8-6.2-.1-2.8 1.1-5.1 3.8-6.1 1.6-.6 3.4-.8 5.2-.8h178.6c1.4 0 2.8.2 4.2.4 3.4.7 4.9 3.1 4.9 6.3.1 3.3-1.4 5.7-4.7 6.6-1.6.4-3.2.5-4.8.5-29.5.1-59.1.1-88.7.1z' />
				</svg>
			)}
		</div>
	)
}

export default Sidebar
