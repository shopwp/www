/** @jsx jsx */
import { jsx, css } from '@emotion/react'
import { useContext } from 'react'
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
import React from 'react'
import Notice from '../../_common/notice'

function Download({ download }) {
	const widerTD = css`
		width: 300px;
	`
	return (
		download && (
			<tr>
				<Td extraCSS={widerTD}>
					{download.name === 'ShopWP' ? 'ShopWP Pro' : download.name}
				</Td>
				<Td>{download.latest_version}</Td>
				<Td>
					<ButtonLink
						download={true}
						text='Download'
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
		width: 130px;
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

function AccountDownloads() {
	const [accountState] = useContext(AccountContext)

	const purchaseLinkCSS = css`
		margin-left: 3px;
	`

	return (
		<>
			<AccountBodyHeader heading='Downloads' />
			<AccountBodyContent>
				{accountState.customer ? (
					Object.keys(accountState.customer.downloads).length !== 0 ? (
						<Downloads downloads={accountState.customer.downloads} />
					) : (
						<Notice type='info'>
							No downloads available yet. You must first{' '}
							<a
								href={wpshopifyMarketing.misc.siteUrl + '/purchase'}
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
