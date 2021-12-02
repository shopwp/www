/** @jsx jsx */
import { jsx, css } from '@emotion/react';

function Td({ children, extraCSS, colspan, align }) {
  const styles = css`
    text-align: ${align ? align : 'left'};
    padding: 8px 15px;
    border: 1px solid #e3e8ee;
    font-size: 15px;

    p {
      margin: 0;
    }
  `;

  return (
    <td colSpan={colspan} css={[styles, extraCSS]} className='wpshopify-table-body-cell'>
      {children}
    </td>
  );
}

export default Td;
