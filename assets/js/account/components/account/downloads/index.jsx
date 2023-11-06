/** @jsx jsx */
import { jsx, css } from '@emotion/react'
import { AccountContext } from '../_state/context'
import AccountBodyHeader from '../body/header'
import AccountBodyContent from '../body/content'
import Table from '../../_common/tables'
import TableBody from '../../_common/tables/body'
import TableHeader from '../../_common/tables/header'
import Th from '../../_common/tables/header/th'
import Td from '../../_common/tables/body/td'
import ButtonLink from '../../_common/button-link'
import { IconDownload } from '../../_common/icons'
import { ContentLoaderBullet } from '../../_common/content-loaders'
import Notice from '../../_common/notice'

function Download({ download }) {
	const widerTD = css`
		width: 300px;
	`

	return (
		download && (
			<tr>
				<Td extraCSS={widerTD}>
					{download.name === 'ShopWP' || download.name === 'WP Shopify Pro'
						? 'ShopWP Pro'
						: download.name}
				</Td>
				<Td>{download.latest_version}</Td>
				<Td>
					<ButtonLink
						download={true}
						text={'Download (' + download.latest_version + ')'}
						href={download.files.file}
						icon={<IconDownload />}
					/>
				</Td>
			</tr>
		)
	)
}

function Downloads({ downloads }) {
	const DownloadsTableCSS = css`
		width: 100%;
		max-width: 100%;
	`

	const narrowCol = css`
		width: 300px;
	`

	return (
		<Table extraCSS={DownloadsTableCSS}>
			<TableHeader>
				<Th extraCSS={narrowCol}>Name</Th>
				<Th extraCSS={narrowCol}>Latest Version</Th>
				<Th>Files</Th>
			</TableHeader>
			<TableBody>
				{Object.keys(downloads).map((download, index) => (
					<Download download={downloads[download]} key={download} />
				))}
			</TableBody>
		</Table>
	)
}

function PreviousDownloads() {
	const DownloadsTableCSS = css`
		width: 100%;
		max-width: 100%;
	`

	const narrowCol = css`
		width: 300px;
	`

	const tableLabel = css`
		margin-top: 20px !important;
		margin-bottom: 5px !important;
	`

	return (
		<>
			<p css={tableLabel}>
				<b>Previous plugin versions:</b>
			</p>
			<Table extraCSS={DownloadsTableCSS}>
				<TableHeader>
					<Th extraCSS={narrowCol}>Name</Th>
					<Th extraCSS={narrowCol}>Version</Th>
					<Th>Files</Th>
				</TableHeader>
				<TableBody>
					<tr>
						<Td>ShopWP Pro</Td>
						<Td>7.1.12</Td>
						<Td>
							<ButtonLink
								download={true}
								text='Download (7.1.12)'
								href='https://wpshop.io/releases/7.1.12/_pro/shopwp-pro-7.1.12.zip'
								icon={<IconDownload />}
							/>
						</Td>
					</tr>
					<tr>
						<Td>ShopWP Pro</Td>
						<Td>8.0.0</Td>
						<Td>
							<ButtonLink
								download={true}
								text='Download (8.0.0)'
								href='https://wpshop.io/releases/8.0.0/_pro/shopwp-pro-8.0.0.zip'
								icon={<IconDownload />}
							/>
						</Td>
					</tr>
					<tr>
						<Td>ShopWP Pro</Td>
						<Td>8.1.6</Td>
						<Td>
							<ButtonLink
								download={true}
								text='Download (8.1.6)'
								href='https://wpshop.io/releases/8.1.6/_pro/shopwp-pro-8.1.6.zip'
								icon={<IconDownload />}
							/>
						</Td>
					</tr>
				</TableBody>
			</Table>
		</>
	)
}

function AccountDownloads() {
	const { useContext } = wp.element
	const [accountState] = useContext(AccountContext)

	const purchaseLinkCSS = css`
		margin-left: 3px;
	`

	return (
		<>
			<AccountBodyHeader
				heading='Downloads'
				totalItems={accountState.customer.downloads.length}
			/>
			<AccountBodyContent>
				{accountState.customer ? (
					Object.keys(accountState.customer.downloads).length !== 0 ? (
						<>
							<Downloads downloads={accountState.customer.downloads} />
							<PreviousDownloads />
						</>
					) : (
						<Notice type='info'>
							No downloads available yet. You must first{' '}
							<a
								href={swp.misc.siteUrl + '/purchase'}
								target='_blank'
								rel='noreferrer'
								css={purchaseLinkCSS}>
								purchase ShopWP Pro
							</a>
							.
						</Notice>
					)
				) : (
					<ContentLoaderBullet />
				)}
			</AccountBodyContent>
		</>
	)
}

export default AccountDownloads
