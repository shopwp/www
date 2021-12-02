/** @jsx jsx */
import { jsx, css } from '@emotion/react';

function ModalBody({ children }) {
  const ModalBodyCSS = css`
    padding: 15px 20px 80px 20px;
    width: calc(100% - 40px);
  `;

  return <div css={ModalBodyCSS}>{children}</div>;
}

export default ModalBody;
