/** @jsx jsx */
import { jsx, css } from '@emotion/react';
import { IconClose } from '../../_common/icons';
import { AccountContext } from '../_state/context';
import { useContext } from 'react';

function ModalHeader({ text }) {
  const [, accountDispatch] = useContext(AccountContext);

  function onModalClose(e) {
    e.preventDefault();
    accountDispatch({ type: 'TOGGLE_MODAL', payload: false });
  }

  const ModalHeader = css`
    border-bottom: 1px solid #e1e1e1;
    padding: 15px 20px;
    background: white;
    width: calc(100% - 40px);
    position: relative;
    display: flex;
    justify-content: space-between;
    line-height: 1;
    align-items: center;
    font-weight: 600;
    font-family: Metropolis;
  `;

  return (
    <div css={ModalHeader}>
      {text} <ModalCloseIcon onModalClose={onModalClose} />
    </div>
  );
}

function ModalCloseIcon({ onModalClose }) {
  const ModalCloseCSS = css`
    background: none;
    border: none;
    outline: none;
    padding: 10px 17px;
    transition: opacity 0.2s ease;
    position: absolute;
    right: 0;

    &:hover {
      cursor: pointer;
      color: #415aff;
    }
  `;

  return (
    <button css={ModalCloseCSS} onClick={onModalClose}>
      <IconClose />
    </button>
  );
}

export default ModalHeader;
