/** @jsx jsx */
import { jsx, css } from '@emotion/react';
import ModalHeader from './header';
import ModalBody from './body';
import Button from '../../_common/button';
import parse from 'html-react-parser';
import { reactivateSubscription } from '../../_common/api';
import Notice from '../../_common/notice';
import to from 'await-to-js';
import { useState } from 'react';

function ModalContentSubscriptionReactivate({ accountState, accountDispatch }) {
  const [isBusy, setIsBusy] = useState(false);

  const smallCSS = css`
    display: block;
    margin-bottom: 15px;
    line-height: 1.5;
  `;

  const altPCSS = css`
    font-size: 15px;
    display: block;
    margin-bottom: 15px;

    strong {
      font-family: Metropolis;
      font-weight: 500;
    }
  `;

  async function onReactivate() {
    setIsBusy(true);

    const [error, resp] = await to(
      reactivateSubscription({ subscription: accountState.subscription })
    );

    setIsBusy(false);

    if (error) {
      console.log('error', error);
    }

    accountDispatch({ type: 'TOGGLE_MODAL', payload: false });

    accountDispatch({
      type: 'SET_NOTICE',
      payload: {
        message:
          'Successfully reactivated subscription to ' + parse(accountState.subscription.name),
        type: 'success',
      },
    });

    accountDispatch({
      type: 'SET_SUBSCRIPTIONS',
      payload: resp,
    });

    setTimeout(function () {
      accountDispatch({
        type: 'SET_NOTICE',
        payload: false,
      });
    }, 5500);
  }

  return (
    <div>
      <ModalHeader text='Reactivate subscription' />
      <ModalBody>
        <Notice type='warning'>Are you sure you want to reactivate your subscription?</Notice>
        <p css={altPCSS}>
          Reactivating subscrption to: <strong>{parse(accountState.subscription.name)}</strong>
        </p>
        <small css={smallCSS}>
          (You will not be charged immediately. You will continue on your previous payment schedule.
          Email us if you have any questions:{' '}
          <a href='mailto:hello@wpshop.io' rel='noreferrer' target='_blank'>
            hello@wpshop.io
          </a>
          )
        </small>
        <Button
          size='small'
          text='Yes, reactivate subscription'
          onClick={onReactivate}
          disabled={isBusy}
        />
      </ModalBody>
    </div>
  );
}

export default ModalContentSubscriptionReactivate;
