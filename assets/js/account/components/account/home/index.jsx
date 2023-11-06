/** @jsx jsx */
import { jsx, css } from '@emotion/react'
import AccountBodyHeader from '../body/header'
import AccountBodyContent from '../body/content'
import { AccountContext } from '../_state/context'
import { SectionCSS } from '../../_common/styles'
import Label from '../../_common/label'
import Table from '../../_common/tables'
import TableBody from '../../_common/tables/body'
import TableHeader from '../../_common/tables/header'
import Td from '../../_common/tables/body/td'
import Th from '../../_common/tables/header/th'
import prettyDate from '../../_common/date'
import Button from '../../_common/button'
import { IconEdit, IconExternal } from '../../_common/icons'
import { ContentLoaderBullet } from '../../_common/content-loaders'
const { useContext } = wp.element

function AccountHome() {
	const [accountState, accountDispatch] = useContext(AccountContext)

	return (
		<>
			<AccountBodyHeader heading='Dashboard' />

			<AccountBodyContent>
				{accountState.customer ? (
					<AccountBodyContentInner
						customer={accountState.customer}
						accountDispatch={accountDispatch}
					/>
				) : (
					<ContentLoaderBullet />
				)}
			</AccountBodyContent>
		</>
	)
}

function AccountBodyContentInner({ customer, accountDispatch }) {
	const NameCSS = css`
		font-size: 20px;
		margin: 0;
		font-weight: bold;
		font-family: Metropolis;
	`

	const EmailCSS = css`
		margin-top: 0;
	`

	const editCSS = css`
		position: absolute;
		top: -43px;
		right: 0;
	`

	const editThCSS = css`
		border: none;
		padding: 0;
	`

	function onProfileEdit(e) {
		e.preventDefault()

		accountDispatch({ type: 'SET_ACTIVE_MODAL_VIEW', payload: 'profileUpdate' })
		accountDispatch({ type: 'TOGGLE_MODAL', payload: true })
	}

	return (
		<div>
			<p css={NameCSS}>{customer.info.name}</p>
			<p css={EmailCSS}>{customer.info.email}</p>
			<div css={SectionCSS}>
				<Label text='Profile:' hasBorder={false} />

				<Table extraCSS={editThCSS}>
					<TableHeader>
						<Th extraCSS={editThCSS}>
							<Button
								text='Edit'
								type='secondary'
								onClick={onProfileEdit}
								extraCSS={editCSS}
								icon={<IconEdit />}
							/>
						</Th>
					</TableHeader>
					<TableBody>
						<tr>
							<Td>Name</Td>
							<Td>{customer.info.name}</Td>
						</tr>
						<tr>
							<Td>Email</Td>
							<Td>{customer.info.email}</Td>
						</tr>
						<tr>
							<Td>Password</Td>
							<Td>
								&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;
							</Td>
						</tr>
					</TableBody>
				</Table>
			</div>
			<div css={SectionCSS}>
				<Label text='General Details:' hasBorder={false} />
				<Table>
					<TableBody>
						<tr>
							<Td>Joined</Td>
							<Td>{prettyDate(customer.info.joined)}</Td>
						</tr>
						<tr>
							<Td>Purchases</Td>
							<Td>{customer.info.purchase_count}</Td>
						</tr>
						<tr>
							<Td>Amount spent</Td>
							<Td>${customer.info.purchase_value}</Td>
						</tr>
						<tr>
							<Td>Slack channel</Td>
							<Td>
								<a
									href='https://join.slack.com/t/wpshopify/shared_invite/zt-m45ab778-Il_xwPwVW~wOeMaFi4l7sg'
									target='_blank'
									rel='noreferrer'>
									ShopWP Slack channel <IconExternal />
								</a>
							</Td>
						</tr>
						<tr>
							<Td>Need Support?</Td>
							<Td>
								<a href='/support/' target='_blank' rel='noreferrer'>
									Submit a support ticket <IconExternal />
								</a>
							</Td>
						</tr>
					</TableBody>
				</Table>
			</div>
		</div>
	)
}

export default AccountHome
