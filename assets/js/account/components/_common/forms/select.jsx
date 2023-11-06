/** @jsx jsx */
import { jsx, css } from '@emotion/react'

function Select({
	children,
	onChange,
	disabled = false,
	label = false,
	defaultVal = false,
}) {
	const inputStyles = css`
		width: 100%;
		display: block;
		padding: 8px 13px;
		font-size: 16px;
		border: 1px solid #868585;
		margin-bottom: 0;
		border-radius: 5px;

		&[disabled] {
			opacity: 0.5;
			background: #f4f4f4;

			&:hover {
				cursor: not-allowed;
			}
		}
	`

	const inputWrapperCSS = css`
		position: relative;

		label {
			font-size: 14px;
			margin-bottom: 5px;
			display: block;
			color: #323232;
		}

		svg {
			position: absolute;
			max-width: 20px;
			top: ${label ? '-13px' : '-2px'};
			right: ${label ? '-2px' : '2px'};
			padding: 10px;

			&:hover {
				cursor: pointer;
			}

			path {
				color: #868686;
			}
		}
	`

	return (
		<div css={inputWrapperCSS}>
			{label && <label>{label}</label>}
			<select
				css={inputStyles}
				onChange={onChange}
				disabled={disabled}
				defaultValue={defaultVal}>
				{children}
			</select>
		</div>
	)
}

export default Select
