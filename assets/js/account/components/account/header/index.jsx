/** @jsx jsx */
import { jsx, css } from '@emotion/react'
import Tippy from '@tippyjs/react'
import { AccountContext } from '../_state/context'
import { ContentLoaderProfile } from '../../_common/content-loaders'
import { logoutUser } from '../../_common/api'
import { IconExternal } from '../../_common/icons'
import to from 'await-to-js'
const { useContext } = wp.element

function AccountIcon({ avatar }) {
	const AccountIconCSS = css`
		position: absolute;
		left: -25px;
		padding: 1px;
		top: 16px;
		border: 1px solid #cdcdcd;
		border-radius: 5px;
		width: 30px;
		height: 30px;
	`

	return avatar.includes('gravatar') === false ? (
		<img src={avatar} css={AccountIconCSS} alt='Profile avatar' />
	) : (
		<img
			src={swp.misc.themeUrl + '/assets/imgs/avatar.svg'}
			css={AccountIconCSS}
			alt='Profile avatar'
		/>
	)
}

function AccountHeaderDropdown() {
	const AccountHeaderDropdownCSS = css`
		list-style: none;
		margin: 0;
		padding: 0;
		display: flex;
		flex-direction: column;
		justify-content: flex-start;
		min-width: 182px;
		background: white;

		a {
			font-family: Metropolis;
			font-weight: 400;
		}

		li:first-of-type a {
			padding-bottom: 7px;
		}

		li:last-of-type a {
			padding-top: 7px;
		}
	`

	const AccountHeaderDropdownLinkCSS = css`
		text-decoration: none;
		color: #0f0728;
		text-align: left;
		display: block;
		width: calc(100% - 40px);
		padding: 15px 20px;
		font-weight: bold;

		svg {
			position: relative;
			top: 3px;
			margin-right: 5px;
		}

		&:hover {
			color: #415aff;
		}
	`

	async function onLogout(e) {
		e.preventDefault()

		const [err, resp] = await to(logoutUser())

		window.location.href = '/login?logout=true'
	}

	return (
		<ul css={AccountHeaderDropdownCSS}>
			<AccountDropdownPages pages={[{ title: 'Profile', link: '/account' }]} />
			<li>
				<a href='#!' onClick={onLogout} css={AccountHeaderDropdownLinkCSS}>
					<svg
						focusable='false'
						role='img'
						xmlns='http://www.w3.org/2000/svg'
						viewBox='0 0 512 512'
						width='15'>
						<path
							fill='currentColor'
							d='M160 217.1c0-8.8 7.2-16 16-16h144v-93.9c0-7.1 8.6-10.7 13.6-5.7l141.6 143.1c6.3 6.3 6.3 16.4 0 22.7L333.6 410.4c-5 5-13.6 1.5-13.6-5.7v-93.9H176c-8.8 0-16-7.2-16-16v-77.7m-32 0v77.7c0 26.5 21.5 48 48 48h112v61.9c0 35.5 43 53.5 68.2 28.3l141.7-143c18.8-18.8 18.8-49.2 0-68L356.2 78.9c-25.1-25.1-68.2-7.3-68.2 28.3v61.9H176c-26.5 0-48 21.6-48 48zM0 112v288c0 26.5 21.5 48 48 48h132c6.6 0 12-5.4 12-12v-8c0-6.6-5.4-12-12-12H48c-8.8 0-16-7.2-16-16V112c0-8.8 7.2-16 16-16h132c6.6 0 12-5.4 12-12v-8c0-6.6-5.4-12-12-12H48C21.5 64 0 85.5 0 112z'></path>
					</svg>
					Logout
				</a>
			</li>
		</ul>
	)
}

function AccountDropdownPages({ pages }) {
	return pages.map(page => <AccountDropdownPage key={page.title} page={page} />)
}

function AccountDropdownPage({ page }) {
	const linkCSS = css`
		text-decoration: none;
		color: #0f0728;
		text-align: left;
		display: block;
		width: calc(100% - 40px);
		padding: 15px 20px;
		text-transform: capitalize;
		font-weight: bold;

		svg {
			position: relative;
			top: 3px;
			margin-right: 5px;
		}

		&:hover {
			color: #415aff;
			cursor: pointer;
		}
	`

	return (
		<li>
			<a href={page.link} css={linkCSS}>
				{page.title}
			</a>
		</li>
	)
}

function AccountHeader() {
	const [accountState] = useContext(AccountContext)

	const ArrowCSS = css`
		width: 9px;
		height: auto;
		position: relative;
		left: 5px;
		top: 4px;
	`

	const AccountHeaderCSS = css`
		margin: 0;
		padding: 0 60px 0 30px;
		background: white;
		width: calc(100% - 90px);
		text-align: right;
		border-bottom: 1px solid #e3e8ee;
		min-height: 70px;
		display: flex;
		justify-content: flex-end;

		> svg {
			position: absolute;
			top: 8px;
			height: 45px;
			right: -65px;
		}

		.tippy-box[data-theme~='light'] {
			box-shadow: none;
			border: 1px solid #ddd;
		}

		.tippy-content {
			padding: 0;
		}

		.tippy-box {
		}
	`

	const AccountHeaderLinkCSS = css`
		text-decoration: none;
		color: #0f0728;
		padding: 20px 0 20px 20px;
		position: relative;
		font-family: Metropolis;
		font-weight: 400;
		font-size: 15px;
		margin-left: 40px;

		@media (max-width: 600px) {
			margin: 0;
			padding: 0;
		}

		&:hover {
			cursor: pointer;
			opacity: 1;
		}
	`

	function onClick(e) {
		e.preventDefault()
	}

	return (
		<header css={AccountHeaderCSS}>
			{accountState.customer.info ? (
				<>
					{accountState.customer.info.isAdmin && <WordPressAdminLink />}
					<SupportLink />
					<SlackLink />
					<DocsLink />
					<Tippy
						content={<AccountHeaderDropdown />}
						allowHTML={true}
						interactive={true}
						theme='light'
						offset={[5, -10]}
						hideOnClick='toggle'
						arrow={false}
						placement='bottom-end'>
						<a href='#!' css={AccountHeaderLinkCSS} onClick={onClick}>
							<AccountIcon avatar={accountState.customer.info.avatar} />
							{accountState.customer.info.name + ' '}

							<svg
								css={ArrowCSS}
								focusable='false'
								role='img'
								xmlns='http://www.w3.org/2000/svg'
								viewBox='0 0 256 512'>
								<path
									fill='currentColor'
									d='M119.5 326.9L3.5 209.1c-4.7-4.7-4.7-12.3 0-17l7.1-7.1c4.7-4.7 12.3-4.7 17 0L128 287.3l100.4-102.2c4.7-4.7 12.3-4.7 17 0l7.1 7.1c4.7 4.7 4.7 12.3 0 17L136.5 327c-4.7 4.6-12.3 4.6-17-.1z'></path>
							</svg>
						</a>
					</Tippy>
				</>
			) : (
				<ContentLoaderProfile />
			)}
		</header>
	)
}

function TopLink({ link, text, external = false, hasHighlight = false }) {
	const TopLinkCSS = css`
		display: flex;
		align-items: center;
		margin-left: 15px;
		font-family: 'Metropolis';
		font-size: 14px;
		position: relative;

		@media (max-width: 900px) {
			display: none;
		}

		a {
			position: relative;
			top: -1px;
			background-color: ${hasHighlight ? '#fff3e8' : 'white'};
			border-radius: ${hasHighlight ? '7px' : '0'};
			padding: ${hasHighlight && external
				? '10px 35px 10px 15px'
				: hasHighlight
				? '8px'
				: '0'};
			text-decoration: ${hasHighlight ? 'none' : 'underline'};
			border: ${hasHighlight ? '1px solid #eec9a7' : 'none'};

			&:hover {
				background-color: ${hasHighlight ? '#ffe6cf' : 'white'};
				opacity: ${hasHighlight ? 1 : '0.6'};
			}
		}

		svg {
			position: ${external && hasHighlight ? 'absolute' : 'static'};
			right: 15px;
			top: 28px;
		}
	`

	return (
		<div css={TopLinkCSS}>
			<a href={link} target={external ? '_blank' : '_self'} rel='noreferrer'>
				{text}
			</a>
			{external ? <IconExternal /> : null}
		</div>
	)
}

function SupportLink() {
	return (
		<TopLink
			link='/support/'
			text='Submit a support ticket'
			hasHighlight={true}
			external={true}
		/>
	)
}

function SlackLink() {
	return (
		<TopLink
			link='https://join.slack.com/t/wpshopify/shared_invite/zt-p3qsqzb5-jrq9n2kY90MgCGALvYoN4Q'
			text='ShopWP Slack Channel'
			external={true}
		/>
	)
}

function WordPressAdminLink() {
	return <TopLink link='/wp-admin' text='Go to /wp-admin' external={true} />
}

function DocsLink() {
	return (
		<TopLink
			link='https://docs.wpshop.io'
			text='Plugin Documentation'
			external={true}
		/>
	)
}

export default AccountHeader
