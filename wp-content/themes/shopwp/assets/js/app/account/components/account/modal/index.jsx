import { useContext } from 'react';
import { AccountContext } from '../_state/context';
import Modal from 'react-modal';
import ModalContentPaymentUpdate from './content-payment-update';
import ModalContentSubscriptionCancel from './content-subscription-cancel';
import ModalContentSubscriptionReactivate from './content-subscription-reactivate';
import ModalContentProfileUpdate from './content-profile-update';

Modal.setAppElement('#root');

function AccountModal() {
  const [accountState, accountDispatch] = useContext(AccountContext);

  const customStyles = {
    overlay: {
      background: 'rgba(193,201,210,.7)',
      transition: 'all 0.2s ease',
    },
    content: {
      width: '500px',
      margin: '0 auto',
      borderRadius: '10px',
      border: '1px solid #b6b6b6',
      padding: '0',
      background: '#f6f9fc',
      boxShadow: '0 7px 14px 0 rgba(60,66,87,.08), 0 3px 6px 0 rgba(0,0,0,.12)',
    },
  };

  function onModalClose() {
    accountDispatch({ type: 'TOGGLE_MODAL', payload: false });
  }

  function setModalContent() {
    switch (accountState.activeModalView) {
      case 'paymentUpdate':
        return (
          <ModalContentPaymentUpdate
            accountState={accountState}
            accountDispatch={accountDispatch}
          />
        );

      case 'subscriptionCancel':
        return (
          <ModalContentSubscriptionCancel
            accountState={accountState}
            accountDispatch={accountDispatch}
          />
        );

      case 'subscriptionReactivate':
        return (
          <ModalContentSubscriptionReactivate
            accountState={accountState}
            accountDispatch={accountDispatch}
          />
        );

      case 'profileUpdate':
        return (
          <ModalContentProfileUpdate
            accountState={accountState}
            accountDispatch={accountDispatch}
          />
        );

      default:
        break;
    }
  }

  return (
    <Modal
      closeTimeoutMS={250}
      isOpen={accountState.isModalOpen}
      onRequestClose={onModalClose}
      contentLabel='Example Modal'
      style={customStyles}>
      {setModalContent()}
    </Modal>
  );
}

export default AccountModal;
