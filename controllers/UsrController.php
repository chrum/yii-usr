<?php

abstract class UsrController extends CController
{
	/**
	 * Sends out an email containing instructions and link to the email verification
	 * or password recovery page, containing an activation key.
	 * @param CFormModel $model it must have a getIdentity() method
	 * @param string $mode 'recovery', 'verify' or 'oneTimePassword'
     * @param PasswordForm $passwordForm
	 * @return boolean if sending the email succeeded
	 */
	public function sendEmail(CFormModel $model, $mode, $passwordForm = null)
	{
        /** @var EMailer $mail */
		$mail = $this->module->mailer;
		$mail->AddAddress($model->getIdentity()->getEmail(), $model->getIdentity()->getName());
		$params = array(
			'siteUrl' => $this->createAbsoluteUrl('/'), 
		);
		switch($mode) {
		default: return false;
		case 'recovery':
		case 'verify':
			$mail->Subject = $mode == 'recovery' ? Yii::t('UsrModule.usr', 'Password recovery') : Yii::t('UsrModule.usr', 'Email address verification');
			$params['actionUrl'] = $this->createAbsoluteUrl('default/'.$mode, array(
				'activationKey'=>$model->getIdentity()->getActivationKey(),
				'email'=>$model->getIdentity()->getEmail(),
                'password'
			));
			break;
        case 'welcome':
            /** @var $model ProfileForm */
            $mail->Subject = Yii::t('UsrModule.usr', 'Dine loginoplysninger');
            $params['username'] = $model->getIdentity();
            $params['password'] = $passwordForm->password;
            $params['full_name'] = $model->firstName. ' '.$model->lastName;
            break;
		case 'oneTimePassword':
			$mail->Subject = Yii::t('UsrModule.usr', 'One Time Password');
			$params['code'] = $model->getNewCode();
			break;
		case 'passwordChanged':
            		$mail->Subject = Yii::t('UsrModule.usr', 'Password changed');
            		$params['username'] = $model->getIdentity();
            		$params['password'] = $passwordForm->newPassword;
            		$params['full_name'] = $model->firstName. ' '.$model->lastName;
            		break;
		}

        $viewPath = $mail->getPathViews().'.'.Yii::app()->language.'.'.$mode;
        if (!file_exists(Yii::getPathOfAlias($viewPath).'.php')) $viewPath = $mail->getPathViews().'.'.$mode;

		$body = $this->renderPartial($viewPath, $params, true);
		$full = $this->renderPartial($mail->getPathLayouts().'.email', array('content'=>$body), true);
		$mail->MsgHTML($full);
		if ($mail->Send()) {
			return true;
		} else {
			Yii::log($mail->ErrorInfo, 'error');
			return false;
		}
	}
}
