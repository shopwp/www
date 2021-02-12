/** @jsx jsx */
import { jsx, css } from '@emotion/react';
import Logo from '../../_common/logo';
import Nav from './nav';

function Sidebar() {
  const NavCSS = css`
    display: flex;
    width: 240px;
    align-items: flex-start;
    background: white;
    flex-direction: column;
    border-right: 1px solid #e3e8ee;
  `;

  return (
    <nav css={NavCSS}>
      <Logo color='#415aff' width='40px' height='40px' />
      <Nav />
    </nav>
  );
}

export default Sidebar;
