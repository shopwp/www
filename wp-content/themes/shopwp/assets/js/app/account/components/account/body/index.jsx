/** @jsx jsx */
import { jsx, css } from '@emotion/react';
import AccountHeader from '../header';

function Body({ children }) {
  const BodyCSS = css`
    flex: 1;
    padding: 0;
  `;

  const BodyInnerCSS = css`
    padding: 20px 40px;
    background: #f6f9fc;
  `;

  return (
    <main css={BodyCSS}>
      <AccountHeader />
      <div css={BodyInnerCSS}>{children}</div>
    </main>
  );
}

export default Body;
