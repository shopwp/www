import { BrowserRouter, Switch, Route } from 'react-router-dom';
import Account from '../account';
import AccountHome from '../account/home';
import AccountLicenses from '../account/licenses';
import AccountSubscriptions from '../account/subscriptions';
import AccountPurchases from '../account/purchases';
import AccountDownloads from '../account/downloads';
import AccountAffiliate from '../account/affiliate';
import { AccountContext } from '../account/_state/context';
import { useEffect, useContext } from 'react';
import React from 'react';

function Bootstrap() {
  const [accountState, accountDispatch] = useContext(AccountContext);

  function getActivePage(pathname) {
    if (pathname === '/') {
      return 'dashboard';
    }
    return pathname.substring(1);
  }

  useEffect(() => {
    accountDispatch({
      type: 'SET_ACTIVE_PAGE',
      payload: getActivePage(window.location.pathname),
    });
  }, [accountDispatch]);

  return (
    <BrowserRouter basename='/account'>
      <Switch>
        <Route
          exact
          path='/'
          render={() => (
            <Account>
              <AccountHome />
            </Account>
          )}
        />

        <Route
          path='/licenses'
          render={() => (
            <Account>
              <AccountLicenses />
            </Account>
          )}
        />

        <Route
          path='/subscriptions'
          render={() => (
            <Account>
              <AccountSubscriptions />
            </Account>
          )}
        />

        <Route
          path='/purchases'
          render={() => (
            <Account>
              <AccountPurchases />
            </Account>
          )}
        />

        <Route
          path='/downloads'
          render={() => (
            <Account>
              <AccountDownloads />
            </Account>
          )}
        />

        <Route
          path='/affiliate'
          render={() => (
            <Account>
              <AccountAffiliate />
            </Account>
          )}
        />

        <Route>{'No route matched!'}</Route>
      </Switch>
    </BrowserRouter>
  );
}

export default Bootstrap;
