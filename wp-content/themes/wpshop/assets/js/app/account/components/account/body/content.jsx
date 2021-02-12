/** @jsx jsx */
import { jsx, css, keyframes } from '@emotion/react';

function AccountBodyContent({ children }) {
  const slideInFromLeft = keyframes`
      0% {
         opacity: 0;
         transform: translateY(15px);
      }
      100% {
         opacity: 1;
         transform: translateY(0px);
      }
  `;

  const AccountBodyContentCSS = css`
    background: white;
    padding: 30px;
    border-radius: 8px;
    animation: ${slideInFromLeft} 0.3s ease-out;
    animation-iteration-count: 1;
    border: 1px solid #e3e8ee;

    > p {
      margin: 0;
    }
  `;

  return <div css={AccountBodyContentCSS}>{children}</div>;
}

export default AccountBodyContent;
