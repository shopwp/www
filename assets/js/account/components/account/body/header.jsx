/** @jsx jsx */
import { jsx, css } from '@emotion/react'

function AccountBodyHeader({ heading, totalItems = null }) {
	const PageHeadingCSS = css`
		font-size: 26px;
		margin-bottom: 7px;
		text-align: left;

		@media (max-width: 600px) {
			font-size: 20px;
		}
	`

	const totalCSS = css`
		display: inline-block;
		margin-left: 4px;
		font-size: 14px;
		font-weight: 100;
		font-family: Inter, arial;
		margin-bottom: 0;
		opacity: 0.6;
	`

	return (
		<h1 css={PageHeadingCSS}>
			{heading}{' '}
			{totalItems === null ? null : (
				<p p css={totalCSS}>
					{' '}
					(Showing: {totalItems})
				</p>
			)}
		</h1>
	)
}

export default AccountBodyHeader
