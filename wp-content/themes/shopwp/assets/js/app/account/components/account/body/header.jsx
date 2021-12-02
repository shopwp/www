/** @jsx jsx */
import { jsx, css } from '@emotion/react';

function AccountBodyHeader({ heading }) {
  const PageHeadingCSS = css`
    font-size: 30px;
  `;
  return <h1 css={PageHeadingCSS}>{heading}</h1>;
}

export default AccountBodyHeader;
