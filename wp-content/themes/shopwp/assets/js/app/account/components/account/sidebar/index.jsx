/** @jsx jsx */
import { jsx, css } from '@emotion/react'
import Logo from '../../_common/logo'
import iconStacked from '../../_common/icons/stack.svg'
import Nav from './nav'

function Sidebar() {
	const NavCSS = css`
		display: flex;
		width: 240px;
		align-items: flex-start;
		background: white;
		flex-direction: column;
		border-right: 1px solid #e3e8ee;

		> img {
			max-width: 188px;
			margin: 0 auto;
			position: relative;
			left: -9px;
		}
	`

	return (
		<nav css={NavCSS}>
			<Logo color='#415aff' width='40px' height='40px' />
			<Nav />
			<img src={iconStacked} alt='' />
		</nav>
	)
}

export default Sidebar
