<?php
$this->pageTitle=Yii::app()->name . ' - Reset Password';
$this->breadcrumbs=array(
	'Reset',
);

Yii::import('bootstrap.widgets.input.*');
?>

<p class="alert alert-info">Please enter your new password.</p>

<div class="form">
	<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'id'=>'password-form',
    'type'=>'horizontal',
	'enableClientValidation'=>true,
	'htmlOptions'=>array('class'=>'well'),
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>


    <?php echo $form->passwordFieldRow($model, 'password', array('class'=>'span3'));?>
    <?php echo $form->hiddenField($model, 'key', array('value'=>$key,'class'=>'span3'));?>
    <?php echo $form->hiddenField($model, 'email', array('value'=>$email,'class'=>'span3'));?>


	<div class="form-actions">
		<?php $this->widget('bootstrap.widgets.TbButton', array('buttonType'=>'submit','type'=>'primary','label'=>'Submit', 'icon'=>'ok'));?>
		<?php $this->widget('bootstrap.widgets.TbButton', array('buttonType'=>'reset','label'=>'Reset'));?>
	</div>

	<?php $this->endWidget(); ?>
</div>
