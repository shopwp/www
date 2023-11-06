/** @jsx jsx */
import { jsx, css } from '@emotion/react'

function Button({
	text,
	onClick,
	disabled,
	extraCSS,
	icon = false,
	type = 'primary',
	size = 'normal',
	loadingText = 'Saving ...',
}) {
	const ButtonCSS = css`
		padding: 0.6em 1.5em 0.7em;
		color: #fff;
		background-color: #0c0e1b;
		border: none;
		position: relative;
		text-decoration: none;
		font-weight: 400;
		font-size: ${size === 'small' ? '15px' : '17px'};
		font-family: Metropolis, arial;
		display: inline-block;
		line-height: 1.1;
		border-radius: 0.4em;
		outline: none;
		text-align: center;
		margin: 0.69444em 0 0;
		transition: all 0.18s ease;
		opacity: 1;
		min-width: 106px;

		.ball-pulse > div {
			width: 7px;
			height: 7px;
		}

		&:hover {
			cursor: pointer;
			text-decoration: none;
			opacity: 1;
			color: #fff;
			background-color: #414141;

			&[disabled] {
				border: none;
				outline: none;
				box-shadow: none;
				cursor: not-allowed;
				background-color: #414141;
			}
		}

		&[disabled] {
			background-color: #bdbdbd;

			&:hover {
				background-color: #bdbdbd;
			}
		}
	`

	const ButtonSecondaryCSS = css`
		background: white;
		box-shadow: rgba(0, 0, 0, 0) 0px 0px 0px 0px,
			rgba(0, 0, 0, 0) 0px 0px 0px 0px, rgba(0, 0, 0, 0.12) 0px 1px 1px 0px,
			rgba(60, 66, 87, 0.16) 0px 0px 0px 1px, rgba(0, 0, 0, 0) 0px 0px 0px 0px,
			rgba(0, 0, 0, 0) 0px 0px 0px 0px, rgba(60, 66, 87, 0.08) 0px 2px 5px 0px;
		outline: none;
		border: none;
		padding: 6px 12px 6px 12px;
		font-size: 15px;
		border-radius: 5px;
		transition: all 0.18s ease;
		font-weight: 400;
		font-family: Metropolis;

		&:hover {
			cursor: pointer;
			background: #eeeeee;
		}

		svg {
			width: 15px;
			margin-left: 8px;
			position: relative;
			top: 1px;
		}
	`

	return (
		<button
			css={[type === 'primary' ? ButtonCSS : ButtonSecondaryCSS, extraCSS]}
			disabled={disabled}
			onClick={onClick}>
			<LoaderWrapper disabled={disabled} loadingText={loadingText} />{' '}
			{!disabled && text}
			{icon && icon}
		</button>
	)
}

function LoaderWrapper({ disabled, loadingText }) {
	const LoaderWrapperCSS = css`
		transition: opacity 0.3s ease-in, transform 0.25s ease-in;
		visibility: ${disabled ? 'visible' : 'hidden'};
		opacity: ${disabled ? 1 : 0};
		transform: ${disabled ? 'translateY(0px)' : 'translateY(10px)'};
		position: ${disabled ? 'static' : 'absolute'};
	`
	return <div css={LoaderWrapperCSS}>{loadingText}</div>
}

export default Button
