/** @jsx jsx */
import { jsx, css } from '@emotion/react';
import { Link } from 'react-router-dom';

function Logo({ color, width, height }) {
  const LogoLink = css`
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 15px;
    width: ${width ? width : '45px'};
    position: relative;
    top: 7px;
  `;

  const LogoCSS = css`
    width: ${height ? height : '45px'};
    height: ${width ? width : '45px'};
  `;

  return (
    <Link to='/'>
      <div css={LogoLink}>
        <svg
          version='1.1'
          id='Layer_1'
          xmlns='http://www.w3.org/2000/svg'
          x='0'
          y='0'
          viewBox='0 0 100 100'
          xmlSpace='preserve'
          css={LogoCSS}>
          <path
            fill={color}
            d='M9.5 26.8h9.4c5.2 0 9.9 2.9 12.3 7.6l10 19.9.1.1 9.1-14.5-5.6-12.4c-.2-.4.1-.8.5-.8h13c5.5 0 10.4 3.2 12.6 8.2l6.9 15.5.7 1.6 1 2.2L90 37.1l3.4-5.4C86.2 15 69.5 3.3 50.2 3.3c-17.4 0-32.6 9.5-40.7 23.5z'></path>
          <path
            fill={color}
            d='M94.6 35L77.2 63.1l-9.8 15.7c-.5.6-1.3.8-2 .4-.6-.4-.8-1.3-.4-1.9l4.5-7.3c-2.8.3-5.8-1-7.2-4L51.8 42.9 29.4 78.8c-.5.6-1.3.8-2 .4-.6-.4-.8-1.3-.4-1.9l4.5-7.2c-2.9.4-5.9-1-7.3-4l-17-34.8c-2.6 5.8-4 12.2-4 19 0 26 21 47 47 47s47-21 47-47c0-5.4-.9-10.5-2.6-15.3z'></path>
        </svg>
      </div>
    </Link>
  );
}

export default Logo;
