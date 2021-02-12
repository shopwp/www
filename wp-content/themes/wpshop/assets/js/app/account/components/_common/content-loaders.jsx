import ContentLoader, { BulletList, Facebook } from 'react-content-loader';
/** @jsx jsx */
import { jsx, css } from '@emotion/react';

function ContentLoaderWrapper({ children }) {
  const ContentLoaderCSS = css`
    max-width: 400px;
  `;
  return <div css={ContentLoaderCSS}>{children}</div>;
}

function ContentLoaderBullet() {
  return (
    <ContentLoaderWrapper>
      <BulletList />
    </ContentLoaderWrapper>
  );
}

function ContentLoaderFacebook() {
  return (
    <ContentLoaderWrapper>
      <Facebook />
    </ContentLoaderWrapper>
  );
}

function ContentLoaderProfile() {
  return (
    <ContentLoader viewBox='0 0 778 116' height={20}>
      <rect x='37' y='34' rx='0' ry='0' width='0' height='0' />
      <rect x='28' y='29' rx='0' ry='0' width='258' height='32' />
      <rect x='28' y='71' rx='0' ry='0' width='465' height='32' />
    </ContentLoader>
  );
}

export { ContentLoaderBullet, ContentLoaderFacebook, ContentLoaderProfile };
