/** @jsx jsx */
import { jsx, css } from '@emotion/react';
import ModalHeader from './header';
import ModalBody from './body';
import Button from '../../_common/button';
import parse from 'html-react-parser';
import { cancelSubscription } from '../../_common/api';
import Notice from '../../_common/notice';
import to from 'await-to-js';
import { useState } from 'react';

function ModalContentSubscriptionCancel({ accountState, accountDispatch }) {
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

  async function onCancel() {
    setIsBusy(true);

    const [error, resp] = await to(cancelSubscription({ subscription: accountState.subscription }));

    if (error) {
      console.log('error', error);
      console.log('resp', resp);
    }

    setIsBusy(false);

    accountDispatch({
      type: 'SET_SUBSCRIPTIONS',
      payload: resp,
    });

    accountDispatch({ type: 'TOGGLE_MODAL', payload: false });

    accountDispatch({
      type: 'SET_NOTICE',
      payload: {
        message: 'Successfully canceled subscription to ' + parse(accountState.subscription.name),
        type: 'success',
      },
    });

    //  setTimeout(function () {
    //    accountDispatch({
    //      type: 'SET_NOTICE',
    //      payload: false,
    //    });
    //  }, 5500);
  }

  return (
    <div>
      <ModalHeader text='Cancel subscription' />
      <ModalBody>
        <Notice type='warning'>Are you sure you want to cancel your subscription?</Notice>
        <p css={altPCSS}>
          Canceling subscrption to: <strong>{parse(accountState.subscription.name)}</strong>
        </p>
        <small css={smallCSS}>
          (You will no longer be charged and your license key will be deactivated
          {accountState.subscription.gateway.includes('paypal')
            ? 'This cannot be reversed. You will need to purchase a new subscription if you wish to use the plugin again. Email us if you have any questions: <a href="mailto:hello@wpshop.io" rel="noreferrer" target="_blank">hello@wpshop.io</a>)'
            : ')'}
        </small>
        <Button size='small' text='Yes, cancel subscription' onClick={onCancel} disabled={isBusy} />
      </ModalBody>
    </div>
  );
}

export default ModalContentSubscriptionCancel;
