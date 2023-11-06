/** @jsx jsx */
import { Global, jsx, css } from '@emotion/react'
import AccountHeader from '../header'

function Body({ children }) {
	const BodyCSS = css`
		flex: 1;
		padding: 0;

		@media (max-width: 600px) {
			> header {
				display: none;
			}
		}
	`

	const BodyInnerCSS = css`
		padding: 10px 40px;
		background: #f6f9fc;

		@media (max-width: 600px) {
			padding: 0 15px 15px 15px;
		}
	`

	return (
		<main css={BodyCSS}>
			<Global
				styles={css`
					body {
						margin: 0;
						padding: 0;
						width: 100%;
					}

					@media (max-width: 600px) {
						.ReactModal__Content {
							width: 100% !important;
							inset: 0 !important;
						}
					}
				`}
			/>
			<AccountHeader />
			<div css={BodyInnerCSS}>{children}</div>
		</main>
	)
}

export default Body
