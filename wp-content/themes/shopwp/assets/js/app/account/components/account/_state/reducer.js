import update from 'immutability-helper'

function AccountReducer(state, action) {
	switch (action.type) {
		case 'SET_CUSTOMER':
			return {
				...state,
				customer: update(state.customer, { $set: action.payload }),
			}

		case 'SET_ACTIVE_PAGE':
			return {
				...state,
				activePage: update(state.activePage, { $set: action.payload }),
			}

		case 'TOGGLE_MODAL':
			return {
				...state,
				isModalOpen: update(state.isModalOpen, { $set: action.payload }),
			}

		case 'SET_ACTIVE_MODAL_VIEW':
			return {
				...state,
				activeModalView: update(state.activeModalView, {
					$set: action.payload,
				}),
			}

		case 'SET_NOTICE':
			return {
				...state,
				notice: update(state.notice, { $set: action.payload }),
			}

		case 'SET_ACTIVE_SUBSCRIPTION':
			return {
				...state,
				subscription: update(state.subscription, { $set: action.payload }),
			}

		case 'UPDATE_CUSTOMER':
			var newCustomer = state.customer

			var newCustomerInfo = update(newCustomer.info, { $merge: action.payload })
			newCustomer.info = newCustomerInfo

			return {
				...state,
				customer: newCustomer,
			}

		case 'SET_SUBSCRIPTIONS':
			return {
				...state,
				subscriptions: update(state.subscriptions, { $set: action.payload }),
			}

		default: {
			throw new Error(`Unhandled action type: ${action.type} in AccountReducer`)
		}
	}
}

export { AccountReducer }
