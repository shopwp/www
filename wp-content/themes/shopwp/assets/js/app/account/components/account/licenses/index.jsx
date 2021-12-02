import AccountBodyHeader from '../body/header'
import AccountBodyContent from '../body/content'
import { useContext, useState } from 'react'
import { AccountContext } from '../_state/context'
/** @jsx jsx */
import { jsx, css } from '@emotion/react'
import { IconCopy, IconRemove } from '../../_common/icons'
import { SectionCSS, StatusCSS } from '../../_common/styles'
import Table from '../../_common/tables'
import TableBody from '../../_common/tables/body'
import Td from '../../_common/tables/body/td'
import Label from '../../_common/label'
import Notice from '../../_common/notice'
import prettyDate from '../../_common/date'
import { deactivateLicense } from '../../_common/api.jsx'
import copy from 'clipboard-copy'
import to from 'await-to-js'
import { ContentLoaderBullet } from '../../_common/content-loaders'
import React from 'react'

function AccountLicenses() {
	const [accountState] = useContext(AccountContext)

	const purchaseLinkCSS = css`
		margin-left: 8px;
	`

	return (
		<>
			<AccountBodyHeader heading='Licenses' />

			<AccountBodyContent>
				{accountState.customer ? (
					accountState.customer.licenses.length ? (
						<Licenses licenses={accountState.customer.licenses} />
					) : (
						<Notice type='info'>
							No licenses keys found!
							<a
								href={wpshopifyMarketing.misc.siteUrl + '/purchase'}
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

function Licenses({ licenses }) {
	return licenses.map((license, index) => {
		return <License key={license.key} license={license} />
	})
}

function License({ license }) {
	const [isCopyingLicense, setIsCopyingLicense] = useState(false)
	const [copyingLicenseMessage, setCopyingLicenseMessage] = useState('Copy')
	const [key] = useState(license.key)
	const [siteCount, setSiteCount] = useState(license.site_count)

	const LicenseKeyCSS = css`
		background-color: #fff;
		padding: 10px 5px 8px 5px;
		border-radius: 5px;
		position: relative;
		font-size: 16px;
		width: 337px;
		letter-spacing: 1px;
		position: relative;
		text-align: center;
		top: 0;
		border: 1px solid #bdbdbd;
		transition: all 0.2s ease;

		&:focus {
			border: 1px solid #ccd7ff;
			outline: none;
		}

		svg {
			position: absolute;
			right: -29px;
			top: 7px;
		}
	`

	const LicenseCSS = css`
		margin-bottom: 1em;
		padding-bottom: 10px;
	`

	const LicenseNameCSS = css`
		font-size: 18px;
		margin: 0;
		margin-right: 15px;
		font-weight: 500;
	`

	const RowCSS = css`
		display: flex;
		background: rgb(2, 0, 36);

		background: linear-gradient(
			180deg,
			rgba(2, 0, 36, 1) 0%,
			rgba(239, 242, 255, 1) 0%,
			rgba(255, 255, 255, 1) 100%
		);
		height: 45px;
		padding: 15px 20px;
		width: calc(100% - 40px);
		border-radius: 8px;
		align-items: baseline;
	`

	const LicenseKeyCopyCSS = css`
		position: absolute;
		right: -85px;
		min-width: 75px;
		top: -13px;
		font-size: 14px;
		opacity: ${isCopyingLicense ? 1 : 0};
		transform: ${isCopyingLicense ? 'translateY(10px)' : 'translateY(20px)'};
		transition: all 0.2s ease;
	`

	const LicenseKeyMeta = css`
		display: flex;
		margin-left: 11px;
		position: relative;
		margin-top: 0;
	`

	const ExtraPaddingSectionCSS = css`
		padding-left: 20px;
	`

	function onMouseEnter(e) {
		setIsCopyingLicense(true)
	}

	function onMouseLeave(e) {
		setIsCopyingLicense(false)
		setCopyingLicenseMessage('Copy')
	}

	async function copyLicense(target) {
		const [err] = await to(copy(target.value))

		if (err) {
			console.error('Error copying license key!')
		}

		setCopyingLicenseMessage('Copied!')
		target.select()

		setTimeout(function () {
			setIsCopyingLicense(false)
		}, 2000)
	}

	function onLicenseClick(e) {
		copyLicense(e.target)
	}

	return (
		<section css={LicenseCSS}>
			<div css={RowCSS}>
				<h2 css={LicenseNameCSS}>
					{license.name === 'ShopWP' ? 'ShopWP Pro' : license.name}
				</h2>

				<input
					css={LicenseKeyCSS}
					onMouseEnter={onMouseEnter}
					onMouseLeave={onMouseLeave}
					onClick={onLicenseClick}
					type='text'
					value={key ? key : ''}
					readOnly
				/>

				<div css={LicenseKeyMeta}>
					<IconCopy />
					<span css={LicenseKeyCopyCSS}>{copyingLicenseMessage}</span>
				</div>
			</div>

			<div css={[SectionCSS, ExtraPaddingSectionCSS]}>
				<LicenseDetails license={license} />
			</div>

			<div css={[SectionCSS, ExtraPaddingSectionCSS]}>
				<Label text={'Activated Sites: ' + siteCount + ' / ' + license.limit} />
				<LicenseSites license={license} setSiteCount={setSiteCount} />
			</div>
		</section>
	)
}

function LicenseDetails({ license }) {
	return (
		<Table>
			<TableBody>
				<tr>
					<Td>Purchased:</Td>
					<Td>{prettyDate(license.purchased)}</Td>
				</tr>
				<tr>
					<Td>Expires:</Td>
					<Td>
						{license.expiration === 'lifetime'
							? 'Lifetime 🎉'
							: prettyDate(license.expiration)}
					</Td>
				</tr>
				<tr>
					<Td>License Term:</Td>
					<Td>1 Year</Td>
				</tr>
				<tr>
					<Td>Activation Limit:</Td>
					<Td>
						{license.site_count} / {license.limit}
					</Td>
				</tr>
				<tr>
					<Td>Status:</Td>
					<Td extraCSS={StatusCSS(license.status)}>
						{license.status === 'expired' ? (
							<LicenseStatusExpired license={license} />
						) : (
							license.status
						)}
					</Td>
				</tr>
			</TableBody>
		</Table>
	)
}

function LicenseStatusExpired({ license }) {
	const LicenseStatusExpiredCSS = css`
		color: black;
		padding-left: 10px;
		font-weight: normal;
	`

	return (
		<div>
			<span>Expired</span>
			<a
				href={
					wpshopifyMarketing.misc.siteUrl +
					'/checkout/?edd_license_key=' +
					license.key +
					'&download_id=' +
					license.download_id
				}
				target='_blank'
				rel='noreferrer'
				css={LicenseStatusExpiredCSS}>
				Renew?
			</a>
		</div>
	)
}

function formatSiteUrl(url) {
	var lastChar = url[url.length - 1]

	if (lastChar === '/') {
		return url.slice(0, -1)
	}

	return url
}

function LicenseSite({ license, site, sites, setSites, setSiteCount }) {
	const [isBusy, setIsBusy] = useState(false)
	const [, accountDispatch] = useContext(AccountContext)

	const LicenseSiteCSS = css`
		margin: 0;
		display: flex;
		flex-direction: row-reverse;
		justify-content: flex-end;
		opacity: ${isBusy ? 0.4 : 1};
		transition: all 0.3s ease;

		> div:first-of-type:hover + div span {
			background: #ffe6cf;
		}
	`

	const LicenseUrlCSS = css`
		span {
			display: inline-block;
			background: #fff3e8;
			padding: 5px 10px 6px 10px;
			border-radius: 5px;
			line-height: 1;
			font-size: 15px;
			color: black;
			text-decoration: none;
			transition: all 0.2s ease;
			border: 1px solid #eec9a7;
		}
	`

	const LicenseUrlRemoveCSS = css`
		padding: 10px 0px;
		position: relative;
		top: -8px;
		flex: 1;
		max-width: 300px;

		&:hover {
			cursor: ${isBusy ? 'not-allowed' : 'pointer'};
		}

		svg {
			position: relative;
			top: 2px;
			margin-left: 14px;

			path {
				fill: black;
			}
		}
		span {
			margin-left: 4px;
			font-size: 15px;
		}

		a {
			margin-left: 5px;
			color: black;
			font-size: 15px;
		}
	`

	const siteUrl = formatSiteUrl(site.url)

	function onDeactiveSite(e) {
		if (isBusy) {
			return
		}

		if (window.confirm('Do you really want to deactivate this site?')) {
			deactivateLicenseKey()
		}
	}

	async function deactivateLicenseKey() {
		setIsBusy(true)

		const [, response] = await to(
			deactivateLicense({
				key: license.key,
				url: siteUrl,
				itemName: license.name,
				itemId: license.download_id,
			})
		)

		setIsBusy(false)

		if (!response.success) {
			console.error('Error removing site!')
		}

		var newstes = sites.filter(s => s.url !== site.url)

		setSites(newstes)
		setSiteCount(newstes.length)

		accountDispatch({
			type: 'SET_NOTICE',
			payload: {
				message: 'Successfully deactivated site: ' + siteUrl,
				type: 'success',
			},
		})

		setTimeout(function () {
			accountDispatch({
				type: 'SET_NOTICE',
				payload: false,
			})
		}, 4500)
	}

	return (
		<li css={LicenseSiteCSS}>
			<div css={LicenseUrlRemoveCSS} onClick={onDeactiveSite}>
				<IconRemove />
				<a href='#!'>Deactivate Site</a>
			</div>
			<div css={LicenseUrlCSS}>
				<span>{siteUrl}</span>
			</div>
		</li>
	)
}

function LicenseSites({ license, setSiteCount }) {
	const [sites, setSites] = useState(license.sites)

	const LicenseSitesCSS = css`
		list-style: none;
		padding: 0;
	`

	const NoticeWrapperCSS = css`
		max-width: 700px;

		svg {
			top: 0;
		}

		a {
			margin-left: 5px;
		}
	`

	return sites.length ? (
		<ul css={LicenseSitesCSS}>
			{sites.map(site => (
				<LicenseSite
					license={license}
					key={site.url}
					site={site}
					setSites={setSites}
					sites={sites}
					setSiteCount={setSiteCount}
				/>
			))}
		</ul>
	) : (
		<div css={NoticeWrapperCSS}>
			<Notice type='info'>
				Your license key is not activated on any sites yet. Learn how to{' '}
				<a href='https://docs.wpshop.io' target='_blank' rel='noreferrer'>
					activate your site
				</a>
				.
			</Notice>
		</div>
	)
}

export default AccountLicenses
