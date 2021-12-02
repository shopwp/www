/** @jsx jsx */
import { jsx, css } from '@emotion/react';

function InputGroup({ children }) {
  const InputGroupCSS = css`
    margin-bottom: 30px;

    p {
      font-size: 15px;
      font-weight: 500;
      font-family: Metropolis;
      margin-bottom: 6px;

      small {
        font-weight: normal;
        font-weight: 400;
        font-family: Inter;
      }
    }

    div + p {
      margin-top: 25px;
    }
  `;

  return <div css={InputGroupCSS}>{children}</div>;
}

export default InputGroup;
