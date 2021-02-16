/** @jsx jsx */
import { jsx, css } from '@emotion/react';
import AccountBodyHeader from '../body/header';
import { useContext } from 'react';
import { AccountContext } from '../_state/context';
import AccountBodyContent from '../body/content';
import Table from '../../_common/tables';
import Notice from '../../_common/notice';
import { IconExternal } from '../../_common/icons';
import TableBody from '../../_common/tables/body';
import TableHeader from '../../_common/tables/header';
import Th from '../../_common/tables/header/th';
import Td from '../../_common/tables/body/td';
import prettyDate from '../../_common/date';
import { StatusCSS } from '../../_common/styles';
import { ContentLoaderBullet } from '../../_common/content-loaders';
import React from 'react';

function Subscription({ subscription }) {
  function prettyGateway(gateway) {
    if (gateway === 'stripe') {
      return 'Credit card';
    }

    return 'PayPal';
  }

  const LastFourCSS = css`
    margin-left: 6px;
    font-size: 87%;
    position: relative;
    top: -1px;
  `;

  return (
    <tr>
      <Td>
        <p dangerouslySetInnerHTML={{ __html: subscription.name }}></p>
      </Td>
      <Td>
        ${subscription.recurring_amount} / {subscription.period}
      </Td>
      <Td extraCSS={StatusCSS(subscription.status)}>{subscription.status}</Td>
      <Td>{prettyDate(subscription.expiration)}</Td>
      <Td>
        {prettyGateway(subscription.gateway)}

        {subscription.card_info && (
          <span css={LastFourCSS}> •••• {subscription.card_info.last4}</span>
        )}
      </Td>

      <Td>
        <SubscriptionActionLinks subscription={subscription} />
      </Td>
    </tr>
  );
}

function SubscriptionActionLinks({ subscription }) {
  const [, accountDispatch] = useContext(AccountContext);

  //   function openPaymentUpdateModal(e) {
  //     e.preventDefault();

  //     accountDispatch({ type: 'SET_ACTIVE_MODAL_VIEW', payload: 'paymentUpdate' });
  //     accountDispatch({ type: 'SET_ACTIVE_SUBSCRIPTION', payload: subscription });
  //     accountDispatch({ type: 'TOGGLE_MODAL', payload: true });
  //   }

  function openSubscriptionCancelModal(e) {
    e.preventDefault();

    accountDispatch({ type: 'SET_ACTIVE_MODAL_VIEW', payload: 'subscriptionCancel' });
    accountDispatch({ type: 'SET_ACTIVE_SUBSCRIPTION', payload: subscription });
    accountDispatch({ type: 'TOGGLE_MODAL', payload: true });
  }

  function openSubscriptionReactivateModal(e) {
    e.preventDefault();

    accountDispatch({ type: 'SET_ACTIVE_MODAL_VIEW', payload: 'subscriptionReactivate' });
    accountDispatch({ type: 'SET_ACTIVE_SUBSCRIPTION', payload: subscription });
    accountDispatch({ type: 'TOGGLE_MODAL', payload: true });
  }

  const SubscriptionActionCSS = css`
    color: black;
    padding: 4px 0;
    display: block;
    position: relative;

    &:hover {
      color: #415aff;
    }
  `;
  return (
    <div>
      {/* {subscription.status !== 'cancelled' && (
        <a href='!#' css={SubscriptionActionCSS} onClick={openPaymentUpdateModal}>
          Update payment method
        </a>
      )} */}

      {subscription.status === 'cancelled' ? (
        subscription.gateway.includes('paypal') || subscription.gateway.includes('manual') ? (
          <PurchaseNewSubscription
            actionCSS={SubscriptionActionCSS}
            onClickCallback={openSubscriptionCancelModal}
          />
        ) : (
          <ReactivateSubscription
            actionCSS={SubscriptionActionCSS}
            onClickCallback={openSubscriptionReactivateModal}
          />
        )
      ) : (
        <CancelSubscription
          actionCSS={SubscriptionActionCSS}
          onClickCallback={openSubscriptionCancelModal}
        />
      )}
    </div>
  );
}

function PurchaseNewSubscription({ actionCSS }) {
  return (
    <a
      href={wpshopifyMarketing.misc.siteUrl + '/purchase'}
      target='_blank'
      rel='noreferrer'
      css={actionCSS}>
      Purchase new subscription <IconExternal />
    </a>
  );
}

function ReactivateSubscription({ actionCSS, onClickCallback }) {
  return (
    <a href='!#' onClick={onClickCallback} css={actionCSS}>
      Reactivate subscription
    </a>
  );
}

function CancelSubscription({ actionCSS, onClickCallback }) {
  return (
    <a href='!#' onClick={onClickCallback} css={actionCSS}>
      Cancel subscription
    </a>
  );
}

function Subscriptions({ subscriptions }) {
  const SubscriptionsTableCSS = css`
    width: 100%;
    max-width: 100%;
  `;

  return (
    <Table extraCSS={SubscriptionsTableCSS}>
      <TableHeader>
        <Th>Subscription</Th>
        <Th>Amount</Th>
        <Th>Status</Th>
        <Th>Renewal Date</Th>
        <Th>Purchase Method</Th>
        <Th>Actions</Th>
      </TableHeader>
      <TableBody>
        {subscriptions.map((subscription) => (
          <Subscription key={subscription.id} subscription={subscription} />
        ))}
      </TableBody>
    </Table>
  );
}

function AccountSubscriptions() {
  const [accountState] = useContext(AccountContext);

  const purchaseLinkCSS = css`
    margin-left: 8px;
  `;

  return (
    <>
      <AccountBodyHeader heading='Subscriptions' />

      <AccountBodyContent>
        {accountState.customer ? (
          accountState.subscriptions.length ? (
            <Subscriptions subscriptions={accountState.subscriptions} />
          ) : (
            <Notice type='info'>
              No subscriptions found!
              <a
                href={wpshopifyMarketing.misc.siteUrl + '/purchase'}
                target='_blank'
                rel='noreferrer'
                css={purchaseLinkCSS}>
                Purchase one today.
              </a>
            </Notice>
          )
        ) : (
          <ContentLoaderBullet />
        )}
      </AccountBodyContent>
    </>
  );
}

export default AccountSubscriptions;
