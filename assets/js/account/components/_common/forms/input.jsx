/** @jsx jsx */
import { jsx, css } from '@emotion/react'
const { useState } = wp.element

function Input({
	type = 'text',
	placeholder = '',
	val = '',
	onChange,
	icon = false,
	disabled = false,
	label = false,
	extraCSS = false,
	pattern = false,
	autocomplete = 'on',
	required = false,
	inputRef = null,
}) {
	const [touched, setTouched] = useState(false)

	function onFocus(e) {
		setTouched(true)
	}

	const inputStyles = css`
		width: calc(100% - 26px);
		display: block;
		padding: 8px 13px;
		font-size: 16px;
		border: 1px solid #868585;
		margin-bottom: 15px;
		border-radius: 5px;
		font-family: Inter;
		font-weight: 400;

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
			font-weight: 500;
			font-family: Metropolis;
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
			<input
				css={[inputStyles, extraCSS]}
				type={type}
				placeholder={placeholder}
				value={val}
				onChange={onChange}
				onFocus={onFocus}
				disabled={disabled}
				pattern={pattern ? pattern : undefined}
				autoComplete={autocomplete}
				ref={inputRef}
			/>

			{icon && icon}

			{!val && touched && required && <MissingNotice />}
		</div>
	)
}

function MissingNotice() {
	const MissingCSS = css`
		margin: -10px 0 0 0;
		color: #ff3860;
		font-weight: normal !important;
		font-size: 15px;
	`

	return <p css={MissingCSS}>This field is required</p>
}

export default Input
