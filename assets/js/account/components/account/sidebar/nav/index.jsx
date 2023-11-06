/** @jsx jsx */
import { jsx, css } from '@emotion/react'
import {
	IconCopy,
	IconBilling,
	IconPurchaseHistory,
	IconDownload,
	IconHome,
	IconExternal,
} from '../../../_common/icons'
import { logoutUser } from '../../../_common/api'
import { AccountContext } from '../../_state/context'
import to from 'await-to-js'
const { useContext } = wp.element
import { Link } from 'react-router-dom'

function NavLinksSecondary({ isMobile }) {
	const NavLinksSecondaryCSS = css`
		margin-top: 0;
		border-top: 1px solid #e3e8ee;
		padding-top: 10px;

		a {
			text-decoration: none;
			font-size: 14px;
			text-transform: capitalize;
			display: block;
			color: #323232;
			padding: 5px 50px 10px 22px;
			font-family: Metropolis;
			font-weight: 400;

			&:hover {
				color: #415aff;
			}
		}
	`

	async function onClick(e) {
		e.preventDefault()

		const [err, resp] = await to(logoutUser())

		window.location.href = '/login?logout=true'
	}

	return (
		<div css={NavLinksSecondaryCSS}>
			<a href={swp.misc.siteUrl + '/support'} target='_blank' rel='noreferrer'>
				Contact <IconExternal />
			</a>
			<a href={swp.misc.siteUrl} target='_blank' rel='noreferrer'>
				Back to site <IconExternal />
			</a>
			{swp.misc.isAdmin ? (
				<a href='/wp-admin/' rel='noreferrer'>
					Go to /wp-admin
				</a>
			) : null}
			{isMobile ? (
				<a href={swp.misc.siteUrl} onClick={onClick} rel='noreferrer'>
					Logout
				</a>
			) : null}
		</div>
	)
}

function Nav({ isOpen }) {
	const [accountState] = useContext(AccountContext)

	const NavCSS = css`
		display: flex;
		flex-direction: column;
		width: 100%;
		margin-top: 30px;
		flex: 1;

		@media (max-width: 600px) {
			display: ${isOpen ? 'block' : 'none'};
			z-index: 99999;
			height: calc(100vh - 0px);
			z-index: 9999;
			top: 40px;
			left: 0;
			background: #f6f9fc;
			position: absolute;
			top: 80px;
			left: 0;
			width: 100%;
			margin: 0;
			height: calc(100vh - 80px);
		}
	`

	return (
		<div css={NavCSS}>
			<NavLinks links={accountState.pages} />
			<NavLinksSecondary isMobile={accountState.isMobile} />
		</div>
	)
}

function NavLinks({ links }) {
	return links.map(link => <NavLink key={link.title} link={link} />)
}

function NavLink({ link }) {
	const [accountState, accountDispatch] = useContext(AccountContext)

	const NavLinkCSS = css`
		margin: 0;

		a {
			font-family: Metropolis;
			text-decoration: none;
			color: ${link.title === accountState.activePage ? '#415aff' : '#0f0728'};
			padding: 15px 50px 15px 22px;
			text-decoration: none;
			font-size: 16px;
			font-weight: 400;
			text-transform: capitalize;
			margin: 0;
			width: calc(100% - 75px);
			display: block;

			&:hover {
				color: #415aff;
			}
		}

		svg {
			width: 16px;
			margin-right: 5px;
			position: relative;
			top: 1px;
		}

		&:hover {
			color: #415aff;
		}

		&:visited {
			color: ${link.title === accountState.activePage ? '#415aff' : '#0f0728'};
		}
	`

	function onClick(event) {
		accountDispatch({ type: 'SET_IS_MOBILE_MENU_OPEN', payload: false })

		accountDispatch({
			type: 'SET_ACTIVE_PAGE',
			payload: link.title,
		})
	}

	function getLink() {
		switch (link.title) {
			case 'dashboard':
				return (
					<Link to='/'>
						<IconHome /> {link.title}
					</Link>
				)
			case 'licenses':
				return (
					<Link to='/licenses'>
						<IconCopy /> {link.title}
					</Link>
				)

			case 'subscriptions':
				return (
					<Link to='/subscriptions'>
						<IconBilling /> {link.title}
					</Link>
				)

			case 'purchases':
				return (
					<Link to='/purchases'>
						<IconPurchaseHistory /> {link.title}
					</Link>
				)

			case 'downloads':
				return (
					<Link to='/downloads'>
						<IconDownload /> {link.title}
					</Link>
				)

			case 'affiliate':
				return (
					<a
						href='https://wpshop.io/affiliates'
						target='_blank'
						style={{ position: 'relative' }}>
						Affiliate Dashboard{' '}
						<IconExternal
							customCSS={{
								marginLeft: '4px',
								position: 'absolute',
								right: '30px',
								top: '21px',
							}}
						/>
					</a>
				)

			default:
				return (
					<Link to='/'>
						<IconHome /> {link.title}
					</Link>
				)
		}
	}

	return (
		<p css={NavLinkCSS} onClick={onClick}>
			{getLink()}
		</p>
	)
}

export default Nav
