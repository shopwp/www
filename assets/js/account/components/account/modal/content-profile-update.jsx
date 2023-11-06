import ModalHeader from './header'
import ModalBody from './body'
import Input from '../../_common/forms/input'
import InputGroup from '../../_common/forms/input-group'
import Button from '../../_common/button'
import { updateProfile } from '../../_common/api'
import { IconEye } from '../../_common/icons'
import Notice from '../../_common/notice'
import to from 'await-to-js'
import update from 'immutability-helper'

const { useState } = wp.element

function ModalContentProfileUpdate({ accountState, accountDispatch }) {
	const [name, setName] = useState(accountState.customer.info.name)
	const [email, setEmail] = useState(accountState.customer.info.email)
	const [oldPassword, setOldPassword] = useState('')
	const [newPassword, setNewPassword] = useState('')
	const [confirmNewPassword, setConfirmNewPassword] = useState('')
	const [passwordVisibility, setPasswordVisibility] = useState('password')
	const [isBusy, setIsBusy] = useState(false)

	const [formNotice, setFormNotice] = useState(false)

	const [profileData, updateProfileData] = useState({
		name: name,
		newEmail: email,
		oldEmail: accountState.customer.info.email,
		oldPassword: oldPassword,
		newPassword: newPassword,
		confirmNewPassword: confirmNewPassword,
	})

	function onSave() {
		saveProfile()
	}

	async function saveProfile() {
		setIsBusy(true)
		setFormNotice(false)

		if (profileData.confirmNewPassword !== profileData.newPassword) {
			setFormNotice({
				message:
					'Your new password does not match. Please try typing the password again.',
				type: 'error',
			})
			setNewPassword('')
			setConfirmNewPassword('')
			setIsBusy(false)
			return
		}

		const [error, response] = await to(updateProfile(profileData))

		setIsBusy(false)
		accountDispatch({ type: 'TOGGLE_MODAL', payload: false })

		if (error) {
			accountDispatch({
				type: 'SET_NOTICE',
				payload: {
					message: JSON.stringify(error),
					type: 'error',
				},
			})

			return
		}

		if (!response.success) {
			accountDispatch({
				type: 'SET_NOTICE',
				payload: {
					message: response.data,
					type: 'error',
				},
			})

			return
		}

		if (response.data.changed_password) {
			location.reload()
		}

		accountDispatch({
			type: 'UPDATE_CUSTOMER',
			payload: {
				email: profileData.newEmail,
				name: profileData.name,
			},
		})

		accountDispatch({
			type: 'SET_NOTICE',
			payload: {
				message: 'Successfully saved profile settings',
				type: 'success',
			},
		})

		setTimeout(function () {
			accountDispatch({
				type: 'SET_NOTICE',
				payload: false,
			})
		}, 5500)
	}

	function onNameChange(e) {
		setName(e.target.value)

		updateProfileData({
			...profileData,
			name: update(profileData.name, { $set: e.target.value }),
		})
	}

	function onEmailChange(e) {
		setEmail(e.target.value)

		updateProfileData({
			...profileData,
			newEmail: update(profileData.newEmail, { $set: e.target.value }),
		})
	}

	function onOldPasswordChange(e) {
		setOldPassword(e.target.value)

		updateProfileData({
			...profileData,
			oldPassword: update(profileData.oldPassword, { $set: e.target.value }),
		})
	}

	function onNewPasswordChange(e) {
		setNewPassword(e.target.value)

		updateProfileData({
			...profileData,
			newPassword: update(profileData.newPassword, { $set: e.target.value }),
		})
	}

	function onConfirmNewPasswordChange(e) {
		setConfirmNewPassword(e.target.value)

		updateProfileData({
			...profileData,
			confirmNewPassword: update(profileData.confirmNewPassword, {
				$set: e.target.value,
			}),
		})
	}

	function changeVisibility() {
		if (passwordVisibility === 'password') {
			setPasswordVisibility('text')
		} else {
			setPasswordVisibility('password')
		}
	}

	return (
		<div>
			<ModalHeader text='Update profile' />
			<ModalBody>
				<InputGroup>
					<p>Name:</p>
					<Input val={name} onChange={onNameChange} disabled={isBusy} />
				</InputGroup>

				<InputGroup>
					<p>Email:</p>
					<Input val={email} onChange={onEmailChange} disabled={isBusy} />
				</InputGroup>

				<InputGroup>
					<p>
						Password: <small>(Leave blank to keep current password)</small>
					</p>
					<Input
						label='Old password'
						val={oldPassword}
						onChange={onOldPasswordChange}
						type={passwordVisibility}
						icon={
							<IconEye
								onClick={changeVisibility}
								visible={passwordVisibility}
							/>
						}
						disabled={isBusy}
					/>
					<Input
						label='New password'
						val={newPassword}
						onChange={onNewPasswordChange}
						type={passwordVisibility}
						disabled={isBusy}
					/>
					<Input
						label='Confirm new password'
						val={confirmNewPassword}
						onChange={onConfirmNewPasswordChange}
						type={passwordVisibility}
						disabled={isBusy}
					/>
				</InputGroup>

				<Button
					size='small'
					text='Update Profile'
					onClick={onSave}
					disabled={isBusy}
				/>

				{formNotice && (
					<Notice type={formNotice.type}>{formNotice.message}</Notice>
				)}
			</ModalBody>
		</div>
	)
}

export default ModalContentProfileUpdate
