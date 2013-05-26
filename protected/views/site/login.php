<?php
$this->pageTitle=Yii::app()->name . ' - Login';
$this->breadcrumbs=array(
	'Login',
);

Yii::import('bootstrap.widgets.input.*');
?>

<p>Please fill out the following form with your login credentials: (username:admin,password:1)</p>

<div class="form">
	<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'id'=>'login-form',
    'type'=>'horizontal',
    'errorMessageCssClass'=>'error',
	'enableClientValidation'=>true,
	'htmlOptions'=>array('class'=>'well'),
	'clientOptions'=>array(
     'validateOnSubmit'=>true,
	),
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->textFieldRow($model, 'username', array('class'=>'span3'));?>
	<?php echo $form->passwordFieldRow($model, 'password', array('class'=>'span3'));?>
    <?php echo $form->error($model,'status'); ?>

    <div class="control-group ">
        <div class="controls"><label for="LoginForm_rememberMe" class="checkbox">
            <input
                type="checkbox" value="0" name="LoginForm[rememberMe]" id="LoginForm_rememberMe">
            Remember me next time<span style="display: none" id="LoginForm_rememberMe_em_"
                                       class="help-inline error"></span></label></div>
    </div>



	<?php // echo $form->checkBoxRow($model, 'rememberMe',array('id'=>'LoginForm_rememberMe')); //this will  not validate W3C?>

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
        <?php $this->widget('bootstrap.widgets.TbButton',
        array('buttonType'=>'link','url'=>$this->createUrl('site/email_for_reset'),'type'=>'inverse','label'=>'Forgot my  password,dammit!', 'icon'=>'exclamation-sign'));?>
	</div>

	<?php $this->endWidget(); ?>
</div><!-- form -->
