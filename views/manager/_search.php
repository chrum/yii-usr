<?php
/* @var $this ManagerController */
/* @var $model SearchForm */
/* @var $form CActiveForm */

$booleanData = array(Yii::t('UsrModule.manager', 'No'), Yii::t('UsrModule.manager', 'Yes'));
$booleanOptions = array('empty'=>Yii::t('UsrModule.manager', 'Any'), 'separator' => '',
    'template'  => '{beginLabel}{input}{labelTitle}{endLabel}',
    "class" => "col-md-5",
    'labelOptions' => array("class" => "col-md-1"));
?>

<div class="wide form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

    <div class="row">
        <div class="col-md-2 control-group form-group">
            <?php echo $form->label($model,'id'); ?>
            <?php echo $form->textField($model,'id', array("class" => "form-control")); ?>
        </div>
        <div class="col-md-5 control-group form-group">
            <?php echo $form->label($model,'username'); ?>
            <?php echo $form->textField($model,'username', array("class" => "form-control")); ?>
        </div>
        <div class="col-md-5 control-group form-group">
            <?php echo $form->label($model,'email'); ?>
            <?php echo $form->textField($model,'email', array("class" => "form-control")); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 control-group form-group">
            <?php echo $form->label($model,'firstName'); ?>
            <?php echo $form->textField($model,'firstName', array("class" => "form-control")); ?>
        </div>
        <div class="col-md-6 control-group form-group">
            <?php echo $form->label($model,'lastName'); ?>
            <?php echo $form->textField($model,'lastName', array("class" => "form-control")); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 control-group form-group">
            <?php echo $form->label($model,'createdOn'); ?>
            <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'model' => $model,
                'attribute' => 'createdOn',
                'htmlOptions' => array(
                    'class=' => 'form-control'
                ),
            )); ?>
        </div>
        <div class="col-md-4 control-group form-group">
            <?php echo $form->label($model,'updatedOn'); ?>
            <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'model' => $model,
                'attribute' => 'updatedOn',
                'htmlOptions' => array(
                    'class=' => 'form-control'
                ),
            )); ?>
        </div>
        <div class="col-md-4 control-group form-group">
            <?php echo $form->label($model,'lastVisitOn'); ?>
            <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'model' => $model,
                'attribute' => 'lastVisitOn',
                'htmlOptions' => array(
                    'class=' => 'form-control'
                ),
            )); ?>
        </div>
    </div>

	<div class="control-group row">
		<?php echo $form->label($model,'emailVerified', array("class" => "col-md-2")); ?>
		<?php echo $form->radioButtonList($model,'emailVerified', $booleanData, $booleanOptions); ?>
	</div>

	<div class="control-group row">
		<?php echo $form->label($model,'isActive', array("class" => "col-md-2")); ?>
		<?php echo $form->radioButtonList($model,'isActive', $booleanData, $booleanOptions); ?>
	</div>

	<div class="control-group row">
		<?php echo $form->label($model,'isDisabled', array("class" => "col-md-2")); ?>
		<?php echo $form->radioButtonList($model,'isDisabled', $booleanData, $booleanOptions); ?>
	</div>

	<div class="buttons">
		<?php echo CHtml::submitButton(Yii::t('UsrModule.manager', 'Search')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- search-form -->
