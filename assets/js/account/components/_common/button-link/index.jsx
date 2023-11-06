/** @jsx jsx */
import { jsx, css } from '@emotion/react'
import { ButtonCSS } from '../styles'

function ButtonLink({ text, href, download = false, icon = false }) {
	const IconCSS = css`
		font-size: 15px;
		min-width: 160px;

		svg {
			margin-left: 10px;
		}
	`

	return (
		<a href={href} css={[ButtonCSS, IconCSS]} download={download}>
			{text}
			{icon && icon}
		</a>
	)
}

export default ButtonLink
