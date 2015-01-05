<?php /*
@var $this DefaultController
@var $model LoginForm */

$title = Yii::t('UsrModule.usr', 'Log in');
if (isset($this->breadcrumbs))
	$this->breadcrumbs=array($this->module->id, $title);
$this->pageTitle = Yii::app()->name.' - '.$title;
?>
<h1><?php echo $title; ?></h1>

<?php $this->widget('usr.components.UsrAlerts', array('cssClassPrefix'=>$this->module->alertCssClassPrefix)); ?>

<div class="<?php echo $this->module->formCssClass; ?>">
<?php $form=$this->beginWidget($this->module->formClass, array(
	'id'=>'login-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
	'focus'=>array($model,'username'),
    'htmlOptions' => array('class' => "form")
)); ?>

	<p class="note"><?php echo Yii::t('UsrModule.usr', 'Fields marked with <span class="required">*</span> are required.'); ?></p>

	<?php echo $form->errorSummary($model); ?>
    <div class="<?php echo $model->hasErrors() ? "has-error" : "" ?>">
        <div class="control-group form-group">
            <?php echo $form->labelEx($model,'username'); ?>
            <?php echo $form->textField($model,'username', array("class" => "form-control")); ?>
            <?php echo $form->error($model,'username', array("class" => "label label-danger")); ?>
        </div>

        <div class="control-group form-group">
            <?php echo $form->labelEx($model,'password'); ?>
            <?php echo $form->passwordField($model,'password', array("class" => "form-control")); ?>
            <?php echo $form->error($model,'password', array("class" => "label label-danger")); ?>
        </div>
    </div>

<?php if ($this->module->rememberMeDuration > 0): ?>
	<div class="rememberMe control-group checkbox">
		<?php echo $form->label($model,'rememberMe', array('label'=>$form->checkBox($model,'rememberMe').$model->getAttributeLabel('rememberMe'), 'class'=>'checkbox')); ?>
		<?php echo $form->error($model,'rememberMe'); ?>
	</div>
<?php endif; ?>

	<div class="buttons">
		<?php echo CHtml::submitButton(Yii::t('UsrModule.usr', 'Log in'), array('class'=>$this->module->submitButtonCssClass." btn btn-primary")); ?>
	</div>
<?php if ($this->module->recoveryEnabled): ?>
	<p>
		<?php echo Yii::t('UsrModule.usr', 'Don\'t remember username or password?'); ?>
		<?php echo Yii::t('UsrModule.usr', 'Go to {link}.', array(
			'{link}'=>CHtml::link(Yii::t('UsrModule.usr', 'password recovery'), array('recovery')),
		)); ?>
	</p>
<?php endif; ?>
<?php if ($this->module->registrationEnabled): ?>
	<p>
		<?php echo Yii::t('UsrModule.usr', 'Don\'t have an account yet?'); ?>
		<?php echo Yii::t('UsrModule.usr', 'Go to {link}.', array(
			'{link}'=>CHtml::link(Yii::t('UsrModule.usr', 'registration'), array('register')),
		)); ?>
	</p>
<?php endif; ?>
<?php if ($this->module->hybridauthEnabled()): ?>
    <p>
        <?php //echo CHtml::link(Yii::t('UsrModule.usr', 'Sign in using one of your social sites account.'), array('hybridauth/login')); ?>
        <?php $this->renderPartial('_login_remote'); ?>
    </p>
<?php endif; ?>

<?php $this->endWidget(); ?>
</div><!-- form -->
