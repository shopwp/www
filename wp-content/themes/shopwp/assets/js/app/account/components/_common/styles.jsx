/** @jsx jsx */
import { jsx, css } from '@emotion/react';

const ButtonCSS = css`
  padding: 0.6em 1.5em 0.7em;
  color: #fff;
  background-color: #415aff;
  border: none;
  position: relative;
  text-decoration: none;
  font-weight: 400;
  font-size: 17px;
  font-family: Metropolis;
  display: inline-block;
  line-height: 1.1;
  border-radius: 0.4em;
  outline: none;
  text-align: center;
  margin: 0;
  transition: all 0.18s ease;
  opacity: 1;

  &:visited,
  &:focus {
    color: white;
  }

  &:hover {
    cursor: pointer;
    text-decoration: none;
    opacity: 1;
    color: #fff;
    box-shadow: 0 0 0 0.24em #cad6ff;
    background-color: #2d45e6;
  }
`;

const SectionCSS = css`
  padding: 0;
`;

function StatusCSS(status) {
  return css`
    color: ${status === 'active'
      ? '#21bc67'
      : status === 'inactive'
      ? '#8d8d8d'
      : status === 'disabled'
      ? '#8d8d8d'
      : '#f24e4e'};
    font-family: Inter;
    font-weight: 400;
    font-style: ${status === 'disabled' ? 'italic' : 'none'};
    text-transform: capitalize;
  `;
}

export { ButtonCSS, SectionCSS, StatusCSS };
