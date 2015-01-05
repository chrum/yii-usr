<?php
/* @var $this ManagerController */
/* @var $model SearchForm */

$this->pageTitle = Yii::t('UsrModule.manager', 'List users');


$csrf = !Yii::app()->request->enableCsrfValidation ? '' : "\n\t\tdata:{ '".Yii::app()->request->csrfTokenName."':'".Yii::app()->request->csrfToken."' },";
$script = <<<JavaScript
var ajaxAction = function() {
	jQuery('#identity-grid').yiiGridView('update', {
		type: 'POST',
		url: jQuery(this).attr('href'),$csrf
		success: function(data) {jQuery('#identity-grid').yiiGridView('update');}
	});
	return false;
};
jQuery('#identity-grid').on('click', 'a.actionButton', ajaxAction);
JavaScript;
Yii::app()->clientScript->registerScript('actions', $script);
?>

<h1><?php echo $this->pageTitle; ?></h1>

<?php $this->widget('usr.components.UsrAlerts', array('cssClassPrefix'=>$this->module->alertCssClassPrefix)); ?>

<p>
<?php echo Yii::t('UsrModule.manager', 'You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b> or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done.'); ?>
</p>

<div id="usersNav">
    <div id="lang">
        <div class="dropdown">
            <button class="btn dropdown-toggle" type="button" id="langMenu" data-toggle="dropdown">
                <?php echo $availableUserTypes[$userType]?>
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu" aria-labelledby="langMenu">
                <?php foreach($availableUserTypes as $code => $name):?>
                    <li role="presentation" class="<?php echo $code == $userType ? "disabled" : "" ?>">
                        <a role="menuitem" tabindex="-1" href="?setUserType=<?php echo $code?>"><?php echo $name?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'identity-grid',
	'dataProvider'=>$model->getIdentity()->getDataProvider($model),
	'filter'=>$model,
    'htmlOptions' => array("class" => "table table-responsive"),
    'itemsCssClass' => 'table table-striped table-hover table-condensed',
    'rowHtmlOptionsExpression' => 'array("data-row-link" => "/usr/manager/update/id/".$data->ID, "class" => "table-row-link")',
    'cssFile' => false,
	'columns'=>array(
        array(
            'name' => 'id',
            'type' => 'number',
            'filter' => false,
            'header' => Yii::t('UsrModule.manager','ID'),
            'value' => '$data->ID',
        ),
		'username:text:'.Yii::t('UsrModule.manager','Username'),
		'email:text:'.Yii::t('UsrModule.manager','Email'),
		'firstName:text:'.Yii::t('UsrModule.manager','Firstname'),
		'lastName:text:'.Yii::t('UsrModule.manager','Lastname'),
	),
)); ?>
