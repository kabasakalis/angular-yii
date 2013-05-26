<?php
$this->pageTitle=Yii::app()->name . ' - Contact Us';
$this->breadcrumbs=array(
	'Contact',
);
?>

<?php if(Yii::app()->user->hasFlash('contact')): ?>
<div class="contact-success alert alert-info">
  <!--   <a class='close' data-dismiss='alertd'>×</a> -->
	<?php echo Yii::app()->user->getFlash('contact'); ?>
</div>
<h1>Contact</h1>
<?php else: ?>
<div class="form">
	<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'id'=>'contact-form',
    'type'=>'horizontal',
	'enableClientValidation'=>true,
	'htmlOptions'=>array('class'=>'well'),
	'clientOptions'=>array(
'validateOnSubmit'=>true,
	),
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

    <?php
       echo $form->errorSummary($model)
         ?>
    <?php echo $form->textFieldRow($model, 'name', array('class'=>'span3'));?>
	<?php echo $form->textFieldRow($model, 'email', array('class'=>'span3'));?>
	<?php echo $form->textFieldRow($model, 'subject', array('class'=>'span3'));?>
    <?php echo $form->textAreaRow($model, 'body', array('class'=>'span3 required'));?>

    <?php if ($model->getRequireCaptcha()) : ?>
     <?php $this->widget('application.extensions.recaptcha.EReCaptcha',
        array('model'=>$user, 'attribute'=>'verify_code',
              'theme'=>'red', 'language'=>'en',
              'publicKey'=>Yii::app()->params['recaptcha_public_key'] ));?>
    <?php echo CHtml::error($model, 'verify_code'); ?>
    <?php endif; ?>

	<div class="form-actions">
		<?php $this->widget('bootstrap.widgets.TbButton', array('buttonType'=>'submit','type'=>'primary','label'=>'Submit', 'icon'=>'ok'));?>
		<?php $this->widget('bootstrap.widgets.TbButton', array('buttonType'=>'reset','label'=>'Reset'));?>
	</div>

	<?php $this->endWidget(); ?>
</div><!-- form -->







<?php endif; ?>