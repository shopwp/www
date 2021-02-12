/** @jsx jsx */
import { jsx, css } from '@emotion/react';
import ModalHeader from './header';
import ModalBody from './body';
import Button from '../../_common/button';
import Input from '../../_common/forms/input';
import InputGroup from '../../_common/forms/input-group';
import Notice from '../../_common/notice';
import { useState, useRef } from 'react';
import { updatePaymentMethod } from '../../_common/api';
import to from 'await-to-js';
import { CountryDropdown, RegionDropdown } from 'react-country-region-selector';
import CreditCardInput from 'react-credit-card-input';

function MissingNotice() {
  const MissingCSS = css`
    margin: 10px 0 0 0;
    color: #ff3860;
    font-weight: normal !important;
    font-size: 15px;
  `;

  return <p css={MissingCSS}>This field is required</p>;
}

function ModalContentPaymentUpdate({ accountState, accountDispatch }) {
  const [isBusy, setIsBusy] = useState(false);
  const [cardNumber, setCardNumber] = useState('');
  const [cvc, setCVC] = useState('');
  const [expiry, setExpiry] = useState('');
  const ccEl = useRef(false);
  const nameOnCardEl = useRef(false);
  const addressLine1El = useRef(false);
  const cityEl = useRef(false);
  const zipEl = useRef(false);
  const countryEl = useRef(false);
  const regionEl = useRef(false);
  const [hasEmptyCountry, setHasEmptyCountry] = useState(false);
  const [hasEmptyRegion, setHasEmptyRegion] = useState(false);

  const [nameOnCard, setNameOnCard] = useState(() =>
    accountState.subscription.card_info.name ? accountState.subscription.card_info.name : ''
  );

  const [addressLine1, setAddressLine1] = useState(() =>
    accountState.subscription.card_info.billing_details.address.line1
      ? accountState.subscription.card_info.billing_details.address.line1
      : ''
  );
  const [addressLine2, setAddressLine2] = useState(() =>
    accountState.subscription.card_info.billing_details.address.line2
      ? accountState.subscription.card_info.billing_details.address.line2
      : ''
  );

  const [city, setCity] = useState(() =>
    accountState.subscription.card_info.billing_details.address.city
      ? accountState.subscription.card_info.billing_details.address.city
      : ''
  );

  const [zip, setZip] = useState(() =>
    accountState.subscription.card_info.billing_details.address.postal_code
      ? accountState.subscription.card_info.billing_details.address.postal_code
      : ''
  );

  const [country, setCountry] = useState(() =>
    accountState.subscription.card_info.billing_details.address.country
      ? accountState.subscription.card_info.billing_details.address.country
      : ''
  );

  const [region, setRegion] = useState(() =>
    accountState.subscription.card_info.billing_details.address.state
      ? accountState.subscription.card_info.billing_details.address.state
      : ''
  );

  function onCardNumberChange(e) {
    setCardNumber(e.target.value);
  }

  function onCVCChange(e) {
    setCVC(e.target.value);
  }

  function onExpiryChange(e) {
    setExpiry(e.target.value);
  }

  function hasMissingRequiredFields() {
    if (
      !cvc ||
      !expiry ||
      !nameOnCard ||
      !cardNumber ||
      !region ||
      !country ||
      !zip ||
      !city ||
      !addressLine1
    ) {
      return true;
    }
    return false;
  }

  function highlightMissingFields() {
    if (!expiry || !cvc || !cardNumber) {
      ccEl.current.cardNumberField.focus();
      ccEl.current.cardNumberField.blur();
    }

    if (!nameOnCard) {
      nameOnCardEl.current.focus();
      nameOnCardEl.current.blur();
    }

    if (!region) {
      setHasEmptyRegion(true);
    } else {
      setHasEmptyRegion(false);
    }

    if (!country) {
      setHasEmptyCountry(true);
    } else {
      setHasEmptyCountry(false);
    }

    if (!zip) {
      zipEl.current.focus();
      zipEl.current.blur();
    }

    if (!city) {
      cityEl.current.focus();
      cityEl.current.blur();
    }

    if (!addressLine1) {
      addressLine1El.current.focus();
      addressLine1El.current.blur();
    }
  }

  function formatExpiry(expiry) {
    var split = expiry.split('/');
    var monthExp = split[0].replace(/\s/g, '');
    var yearExp = split[1].replace(/\s/g, '');

    return [monthExp, yearExp];
  }

  async function onUpdate() {
    if (hasMissingRequiredFields()) {
      highlightMissingFields();
      return;
    }

    setIsBusy(true);

    const [monthExp, yearExp] = formatExpiry(expiry);

    const [error] = await to(
      updatePaymentMethod({
        subscription: accountState.subscription,
        monthExp: monthExp,
        yearExp: yearExp,
        nameOnCard: nameOnCard,
        cvc: cvc,
        cardNumber: cardNumber,
        region: region,
        country: country,
        zip: zip,
        city: city,
        addressLine1: addressLine1,
        addressLine2: addressLine2,
      })
    );
    setIsBusy(false);

    if (error) {
      return;
    }

    accountDispatch({ type: 'TOGGLE_MODAL', payload: false });

    accountDispatch({
      type: 'SET_NOTICE',
      payload: {
        message: 'Successfully updated payment method',
        type: 'success',
      },
    });

    setTimeout(function () {
      accountDispatch({
        type: 'SET_NOTICE',
        payload: false,
      });
    }, 5500);

    return;
  }

  const selectInlineCSS = css`
    display: flex;

    > div {
      margin-right: 10px;
      width: 50%;
    }
  `;

  const SelectParentCSS = css`
    select {
      width: 100%;
      display: block;
      padding: 8px 13px;
      font-size: 16px;
      border: 1px solid #868585;
      margin-bottom: 0;
      border-radius: 5px;
    }
  `;

  const CreditCardParentCSS = css`
    opacity: ${isBusy ? 0.5 : 1};
    background: ${isBusy ? '#f4f4f4' : 'none'};

    &:hover {
      cursor: ${isBusy ? 'not-allowed' : 'text'};

      input,
      label {
        cursor: ${isBusy ? 'not-allowed' : 'text'};
      }
    }

    > div {
      width: 100%;
      margin-bottom: 0;
    }

    .input + p {
      font-weight: normal;
    }

    div + p {
      margin-top: 8px;
    }

    #field-wrapper {
      border: 1px solid #868585;
    }
  `;

  return (
    <div>
      <ModalHeader text='Update payment method' />
      <ModalBody>
        <Notice type='info' multiLine={true}>
          You will be replacing your current credit card (••••{' '}
          {accountState.subscription.card_info.last4}) with a new one below. You will not be
          charged.
        </Notice>

        <InputGroup>
          <p>Name on Card:</p>
          <Input
            inputRef={nameOnCardEl}
            val={nameOnCard}
            onChange={(e) => setNameOnCard(e.target.value)}
            disabled={isBusy}
            required={true}
          />
        </InputGroup>

        <InputGroup>
          <p>Card Details:</p>
          <div css={CreditCardParentCSS}>
            <CreditCardInput
              ref={ccEl}
              cardNumberInputProps={{ value: cardNumber, onChange: onCardNumberChange }}
              cardExpiryInputProps={{ value: expiry, onChange: onExpiryChange }}
              cardCVCInputProps={{ value: cvc, onChange: onCVCChange }}
              fieldClassName='input'
            />
          </div>
        </InputGroup>

        <InputGroup>
          <p>Billing Address:</p>
          <Input
            inputRef={addressLine1El}
            val={addressLine1}
            onChange={(e) => setAddressLine1(e.target.value)}
            disabled={isBusy}
            required={true}
          />

          <p>Billing Address Line 2:</p>
          <Input
            val={addressLine2}
            onChange={(e) => setAddressLine2(e.target.value)}
            disabled={isBusy}
          />

          <div css={selectInlineCSS}>
            <div>
              <p>Billing City:</p>
              <Input
                inputRef={cityEl}
                val={city}
                onChange={(e) => setCity(e.target.value)}
                disabled={isBusy}
                required={true}
              />
            </div>
            <div>
              <p>Billing Zip / Postal Code:</p>
              <Input
                inputRef={zipEl}
                val={zip}
                onChange={(e) => setZip(e.target.value)}
                disabled={isBusy}
                required={true}
              />
            </div>
          </div>

          <div css={selectInlineCSS}>
            <div css={SelectParentCSS} ref={countryEl}>
              <p>Billing Country:</p>
              <CountryDropdown
                value={country}
                onChange={(val) => {
                  setHasEmptyCountry(false);
                  setCountry(val);
                }}
                disabled={isBusy}
                valueType='short'
              />
              {hasEmptyCountry && <MissingNotice />}
            </div>
            <div css={SelectParentCSS} ref={regionEl}>
              <p>Billing State / Province:</p>
              <RegionDropdown
                country={country}
                value={region}
                onChange={(val) => {
                  setHasEmptyRegion(false);
                  setRegion(val);
                }}
                disabled={isBusy}
                countryValueType='short'
                valueType='short'
              />
              {hasEmptyRegion && <MissingNotice />}
            </div>
          </div>
        </InputGroup>

        <Button size='small' text='Update Payment Method' onClick={onUpdate} disabled={isBusy} />
      </ModalBody>
    </div>
  );
}

export default ModalContentPaymentUpdate;
