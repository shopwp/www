/** @jsx jsx */
import { jsx, css } from '@emotion/react';

function Th({ children, extraCSS, align, colspan }) {
  const styles = css`
    text-align: ${align ? align : 'left'};
    padding: 8px 16px;
    border: 1px solid #e7e7e7;
  `;

  return (
    <th colSpan={colspan} css={[styles, extraCSS]} className='wpshopify-table-header-cell'>
      {children}
    </th>
  );
}

export default Th;
