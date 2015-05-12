<?php

Yii::import('usr.controllers.UsrController');

class ManagerController extends UsrController
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	//public $layout='//layouts/column2';
	public $layout='//layouts/main';
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu=array();

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl',
			//'postOnly + delete,verify,activate,disable',
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow', 'actions'=>array('index'), 'roles'=>array('usr.read')),
			array('allow', 'actions'=>array('update', 'bulkAdd'), 'users'=>array('@')),
			array('allow', 'actions'=>array('delete'), 'roles'=>array('usr.delete')),
			array('allow', 'actions'=>array('verify', 'activate', 'disable'), 'roles'=>array('usr.update.status')),
			array('deny', 'users'=>array('*')),
		);
	}

    /**
     * @inheritdoc
     */
    protected function afterAction($action)
    {
        if (in_array($action->id, array('delete', 'verify', 'activate', 'disable'))) {
            if(!isset($_GET['ajax']))
                $this->redirect(isset($_REQUEST['returnUrl']) ? $_REQUEST['returnUrl'] : array('index'));
        }
    }

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'index' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id=null)
	{
		if (!Yii::app()->user->checkAccess($id === null ? 'usr.create' : 'usr.update')) {
			throw new CHttpException(403, Yii::t('yii','You are not authorized to perform this action.'));
		}

		/** @var ProfileForm */
		$profileForm = $this->module->createFormModel('ProfileForm', 'register');
		$profileForm->detachBehavior('captcha');
		if ($id !== null) {
			$profileForm->setIdentity($identity=$this->loadModel($id));
			$profileForm->setAttributes($identity->getAttributes());
		}
		/** @var PasswordForm */
		$passwordForm = $this->module->createFormModel('PasswordForm', 'register');

		if(isset($_POST['ajax']) && $_POST['ajax']==='profile-form') {
			echo CActiveForm::validate($profileForm);
			Yii::app()->end();
		}
		/**
		 * @todo Check for detailed auth items
		 */
		$canUpdateAttributes = Yii::app()->user->checkAccess('usr.update.attributes');
		$canUpdatePassword = Yii::app()->user->checkAccess('usr.update.password');
		$canUpdateAuth = Yii::app()->user->checkAccess('usr.update.auth');

		if(isset($_POST['ProfileForm'])) {
			$profileForm->setAttributes($_POST['ProfileForm']);
			if ($profileForm->getIdentity() instanceof IPictureIdentity && !empty($profileForm->pictureUploadRules)) {
				$profileForm->picture = CUploadedFile::getInstance($profileForm, 'picture');
			}
			if ($canUpdatePassword && isset($_POST['PasswordForm']) && isset($_POST['PasswordForm']['newPassword']) && ($p=trim($_POST['PasswordForm']['newPassword']))!=='') {
				$passwordForm->setAttributes($_POST['PasswordForm']);
				$updatePassword = true;
			} else {
				$updatePassword = false;
			}
			if ($profileForm->validate() && (!$updatePassword || $passwordForm->validate())) {
				$trx = Yii::app()->db->beginTransaction();
				$oldEmail = $profileForm->getIdentity()->getEmail();
				if (($canUpdateAttributes && !$profileForm->save($this->module->requireVerifiedEmail)) || ($updatePassword && !$passwordForm->resetPassword($profileForm->getIdentity()))) {
					$trx->rollback();
					Yii::app()->user->setFlash('error', Yii::t('UsrModule.usr', 'Failed to register a new user.').' '.Yii::t('UsrModule.usr', 'Try again or contact the site administrator.'));
				} else {
					if ($canUpdateAuth) {
						$identity = $profileForm->getIdentity();
						$authManager = Yii::app()->authManager;
						$assignedRoles = $id === null ? array() : $authManager->getAuthItems(CAuthItem::TYPE_ROLE, $id);

						if (isset($_POST['roles']) && $_POST['roles'] == "") {
						    unset($_POST['roles']);
						}
						if (isset($_POST['roles'])) {
					    if (!is_array($_POST['roles'])) {
					        $_POST['roles'] = array($_POST['roles']);
					    }
							foreach($_POST['roles'] as $roleName) {
								if (!isset($assignedRoles[$roleName])) {
									$authManager->assign($roleName, $identity->getId());
								} else {
									unset($assignedRoles[$roleName]);
								}
							}
						}
						foreach($assignedRoles as $roleName=>$role) {
							$authManager->revoke($roleName, $identity->getId());
						}
					}
					$trx->commit();
					if ($this->module->requireVerifiedEmail && $oldEmail != $profileForm->getIdentity()->email) {
						if ($this->sendEmail($profileForm, 'verify')) {
							Yii::app()->user->setFlash('success', Yii::t('UsrModule.usr', 'An email containing further instructions has been sent to the provided email address.'));
						} else {
							Yii::app()->user->setFlash('error', Yii::t('UsrModule.usr', 'Failed to send an email.').' '.Yii::t('UsrModule.usr', 'Try again or contact the site administrator.'));
						}
					}
					if (!Yii::app()->user->hasFlash('success')) {
						Yii::app()->user->setFlash('success', Yii::t('UsrModule.manager', 'User account has been successfully created or updated.'));
					}
					$this->redirect(array('index'));
				}
			}
		}

		$this->render('update', array('id'=>$id, 'profileForm'=>$profileForm, 'passwordForm'=>$passwordForm));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		if (!$this->loadModel($id)->delete()) {
			throw new CHttpException(409,'User account could not be deleted.');
		}

		// if AJAX request (triggered by deletion via index grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
	}

	/**
	 * Toggles email verification status for a particular user.
	 * @param integer $id the ID of the user which email verification status is to be toggled
	 */
	public function actionVerify($id)
	{
		$this->loadModel($id)->toggleStatus(IManagedIdentity::STATUS_EMAIL_VERIFIED);

		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('update', "id" => $id));
	}

	/**
	 * Toggles active status for a particular user.
	 * @param integer $id the ID of the user which active status is to be toggled
	 */
	public function actionActivate($id)
	{
		$this->loadModel($id)->toggleStatus(IManagedIdentity::STATUS_IS_ACTIVE);

		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('update', "id" => $id));
	}

	/**
	 * Toggles disabled status for a particular user.
	 * @param integer $id the ID of the user which disabled status is to be toggled
	 */
	public function actionDisable($id)
	{
		$this->loadModel($id)->toggleStatus(IManagedIdentity::STATUS_IS_DISABLED);

		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('update', "id" => $id));
	}

	/**
	 * Manages all models.
	 */
	public function actionIndex()
	{
        $availableUserTypes = array(
            "all" => "All Users",
            "admin" => "Administrators",
            "manager" => "Managers",
            "user" => "Regular users"
        );
		if (isset($_REQUEST['setUserType']) && isset($availableUserTypes[$_REQUEST['setUserType']])) {
            $userType = $_SESSION['userType'] = $_REQUEST['setUserType'];

        } else if (isset($_SESSION['userType'])) {
            $userType = $_SESSION['userType'];

        } else {
            $userType = 'all';
        }

        if ($userType != "all") {
            if ($userType == "user") {
                $excludedIds = array();
                $users = AuthAssignment::model()->findAllByAttributes(
                    array(),
                    "itemname IN ('admin', 'manager')"
                );
                if (count($users) > 0) {
                    foreach($users as $usr) {
                        $excludedIds[] = $usr->userid;
                    }
                }

            } else {
                $ids = array();
                $users = AuthAssignment::model()->findAllByAttributes(array(
                    "itemname" => $userType
                ));
                if (count($users) > 0) {
                    foreach($users as $usr) {
                        $ids[] = $usr->userid;
                    }
                }
            }

        }

        $model = $this->module->createFormModel('SearchForm');
        if (isset($ids)) {
            $model->id = $ids;
        }
        if (isset($excludedIds)) {
            $model->excludedIds = $excludedIds;
        }

		if (isset($_GET['SearchForm'])) {
			$model->attributes = $_GET['SearchForm'];
			$model->validate();
			$errors = $model->getErrors();
			$model->unsetAttributes(array_keys($errors));
		}

		$this->render('index', array(
            'model'=>$model,
            'userType' => $userType,
            'availableUserTypes' => $availableUserTypes
        ));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return User the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$searchForm = $this->module->createFormModel('SearchForm');
		if(($model = $searchForm->getIdentity($id))===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

    public function actionBulkAdd()
    {
        if (isset($_REQUEST['name']) && $_REQUEST['name'] != "" &&
            isset($_REQUEST['password']) && $_REQUEST['password'] != "" &&
            isset($_REQUEST['amount']))
        {
            $name = $_REQUEST['name'];
            $password = $_REQUEST['password'];
            $appendId = isset($_REQUEST['password_with_id']) ? true : false;
            $amount = intval($_REQUEST['amount']);
            if($amount == 0) {
                $amount = 1;
            }

            $data = array();
            $added = 0;
            for($i = 1; $i <= $amount; $i++) {
                $user = new stdClass();
                $user->username = $name.$i;
                $user->password = $password.($appendId ? $i : "");
                $user->email = $name.$i."@test.te";
                if (isset($_REQUEST["create"])) {
                    $newUser = new User();
                    $newUser->setAttributes((array)$user);
                    $newUser->password = User::hashPassword($user->password);
                    $newUser->firstname = $user->username;
                    $newUser->is_active = 1;
                    if ($newUser->validate()) {
                        //$newUser->save(false);
                        $added++;
                    }

                } else {
                    $user->id = $i;
                    $data[] = $user;
                }
            }

            if (isset($_REQUEST["create"])) {
                if ($added == $amount) {
                    Yii::app()->user->setFlash('success', "Successfully added $added new user(s).");

                } else if ($added > 0){
                    Yii::app()->user->setFlash('warning', "Successfully added $added from $amount new user(s).");

                } else {
                    Yii::app()->user->setFlash('error', "Successfully added $added new user(s).");
                }
                unset($name, $password, $appendId, $amount);

            } else {
                $q = new CDbCriteria();
                $q->addSearchCondition('username', $name);
                $existing = User::model()->findAll($q);
                $existingUsernames = array();
                $existingEmails = array();
                foreach($existing as $user) {
                    $existingUsernames[] = $user->username;
                    $existingEmails[] = $user->email;
                }
                for($i = 0; $i < count($data); $i++) {
                    if (in_array($data[$i]->username, $existingUsernames)) {
                        $data[$i]->error = "Username exists, cant create this user";

                    } else if (in_array($data[$i]->email, $existingEmails)) {

                    }
                }
            }
        }


        $this->render('bulkAdd', array(
            'name' => isset($name) ? $name : '',
            'password' => isset($password) ? $password : '',
            'appendId' => isset($appendId) ? $appendId : false,
            'amount' => isset($amount) ? $amount : 1,
            'data' => isset($data) ? $data : false
        ));
    }
}
