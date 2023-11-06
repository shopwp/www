/** @jsx jsx */
import { jsx, css } from '@emotion/react'

function Label({ text, hasBorder }) {
	const LabelCSS = css`
		font-size: 16px;
		font-weight: 600;
		margin-bottom: 7px;
		margin-top: 30px;
		border-bottom: ${hasBorder === false ? 'none' : '1px solid #e3e8ee'};
		padding-bottom: 0;
		font-weight: 400;
		font-family: Metropolis;
	`

	return <h3 css={LabelCSS}>{text}</h3>
}

export default Label
