<?php
/**
 * Created by PhpStorm.
 * User: chrystian
 * Date: 5/11/15
 * Time: 7:06 PM
 */

$this->pageTitle = Yii::t('UsrModule.manager', 'Bulk add');
$pageSize = 100;
?>
<?php $this->widget('usr.components.UsrAlerts', array('cssClassPrefix'=>$this->module->alertCssClassPrefix)); ?>
<h1>
    <?php echo $this->pageTitle; ?>
</h1>

<?php echo TbHtml::beginFormTb(TbHtml::FORM_LAYOUT_HORIZONTAL); ?>
    <?php if($data == null):?>
        <div class="row">
            <?php echo TbHtml::label('Name', 'name'); ?>
            <?php echo TbHtml::textField('name', $name, array('placeholder' => 'Usernames base')); ?>
        </div>
        <div class="row">
            <?php echo TbHtml::label('Password', 'password'); ?>
            <?php echo TbHtml::textField('password', $password, array('placeholder' => 'Password')); ?>
        </div>
            <?php echo TbHtml::checkBox('password_with_id', $appendId, array('label' => 'Append id to user password')); ?>
        <div class="row">
            <?php echo TbHtml::label('Amount', 'amount'); ?>
            <?php echo TbHtml::textField('amount', $amount, array('type' => 'number')); ?>
        </div>

        <div class="row">
            <?php echo TbHtml::submitButton('Preview'); ?>
        </div>


    <?php else:?>
        <?php
        $dataProvider = new CArrayDataProvider($data, array(
            'id'=>'newUsers',
            'sort'=>array(
                'attributes'=>array(
                    'id', 'username', 'email', 'password', 'error'
                ),
            ),
            'pagination'=>array(
                'pageSize' => $pageSize
            ),
        ));
        ?>
        <?php $this->widget('bootstrap.widgets.TbGridView', array(
                'dataProvider' => $dataProvider,
                'template' => "{items}",
                'enableSorting' => false,
                'columns' => array(
                    array(
                        'name' => 'id',
                        'header' => '#',
                        'htmlOptions' => array('color' =>'width: 60px'),
                    ),
                    array(
                        'name' => 'username',
                        'header' => 'Name',
                    ),
                    array(
                        'name' => 'email',
                        'header' => 'Email',
                    ),
                    array(
                        'name' => 'password',
                        'header' => 'Password',
                    ),
                    array(
                        'name' => 'error',
                        'header' => 'Errors',
                    ),
                ),
            )); ?>

        <?php if(count($data) > $pageSize): ?>
            <div class="row">
                And so on... last username is <strong><?php echo $name.count($data)?></strong>
            </div>
        <?php endif;?>

        <div class="row">
            <?php echo CHtml::hiddenField("name", $name)?>
            <?php echo CHtml::hiddenField("password", $password)?>
            <?php echo CHtml::hiddenField("password_with_id", $appendId)?>
            <?php echo CHtml::hiddenField("amount", $amount)?>
            <?php echo CHtml::hiddenField("create", true)?>
            <a href="/usr/manager/bulkAdd" class="btn btn-default" 1type="button">Reset</a>
            <?php echo TbHtml::submitButton('Create', array('color' => TbHtml::BUTTON_COLOR_DANGER)); ?>
        </div>
    <?php endif?>

<?php echo TbHtml::endForm(); ?>
