<?php /*
@var $this CController
@var $model ProfileForm
@var $passwordForm PasswordForm
 */
?>

<div class="row">
    <div class="control-group form-group col-md-6">
        <?php echo $form->labelEx($model,'username'); ?>
        <?php echo $form->textField($model,'username', array("class" => "form-control")); ?>
        <?php echo $form->error($model,'username', array("class" => "label label-danger")); ?>
    </div>
    <div class="control-group form-group col-md-6">
        <?php echo $form->labelEx($model,'email'); ?>
        <?php echo $form->textField($model,'email', array("class" => "form-control")); ?>
        <?php echo $form->error($model,'email', array("class" => "label label-danger")); ?>
    </div>
</div>


<?php if ($model->scenario !== 'register'): ?>
	<div class="control-group form-group">
		<?php echo $form->labelEx($model,'password'); ?>
		<?php echo $form->passwordField($model,'password', array('autocomplete'=>'off', "class" => "form-control")); ?>
		<?php echo $form->error($model,'password', array("class" => "label label-danger")); ?>
	</div>
<?php endif; ?>

<?php if (isset($passwordForm) && $passwordForm !== null): ?>
<?php $this->renderPartial('/default/_newpassword', array('form'=>$form, 'model'=>$passwordForm)); ?>
<?php endif; ?>

<div class="row">
    <div class="control-group form-group col-md-6">
        <?php echo $form->labelEx($model,'firstName'); ?>
        <?php echo $form->textField($model,'firstName', array("class" => "form-control")); ?>
        <?php echo $form->error($model,'firstName', array("class" => "label label-danger")); ?>
    </div>
    <div class="control-group form-group col-md-6">
        <?php echo $form->labelEx($model,'lastName'); ?>
        <?php echo $form->textField($model,'lastName', array("class" => "form-control")); ?>
        <?php echo $form->error($model,'lastName', array("class" => "label label-danger")); ?>
    </div>
</div>

<?php if ($model->getIdentity() instanceof IPictureIdentity && !empty($model->pictureUploadRules)):
	$picture = $model->getIdentity()->getPictureUrl(80,80);
	if ($picture !== null) {
		$url = $picture['url'];
		unset($picture['url']);
	}
?>
	<div class="control-group">
		<?php echo $form->labelEx($model,'picture'); ?>
		<?php echo $picture === null ? '' : CHtml::image($url, Yii::t('UsrModule.usr', 'Profile picture'), $picture); ?><br/>
		<?php echo $form->fileField($model,'picture'); ?>
		<?php echo $form->error($model,'picture'); ?>
	</div>
	<div class="control-group">
		<?php echo $form->label($model,'removePicture', array('label'=>$form->checkBox($model,'removePicture').$model->getAttributeLabel('removePicture'), 'class'=>'checkbox')); ?>
		<?php echo $form->error($model,'removePicture'); ?>
	</div>
<?php endif; ?>
