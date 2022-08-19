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
import { AccountContext } from '../../_state/context'
import { useContext } from 'react'
import { Link } from 'react-router-dom'

function NavLinksSecondary() {
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
			padding: 5px 50px 10px 15px;
			font-family: Metropolis;
			font-weight: 400;

			&:hover {
				color: #415aff;
			}
		}
	`

	return (
		<div css={NavLinksSecondaryCSS}>
			<a
				href={wpshopifyMarketing.misc.siteUrl + '/contact'}
				target='_blank'
				rel='noreferrer'>
				Contact <IconExternal />
			</a>
			<a
				href={wpshopifyMarketing.misc.siteUrl}
				target='_blank'
				rel='noreferrer'>
				Back to site <IconExternal />
			</a>
		</div>
	)
}

function Nav() {
	const [accountState] = useContext(AccountContext)

	const NavCSS = css`
		display: flex;
		flex-direction: column;
		width: 100%;
		margin-top: 30px;
		flex: 1;
	`

	return (
		<div css={NavCSS}>
			<NavLinks links={accountState.pages} />
			<NavLinksSecondary />
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
			padding: 15px 50px 15px 15px;
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
