<?php

/**
 * ContactForm class.
 * ContactForm is the data structure for keeping
 * contact form data. It is used by the 'contact' action of 'SiteController'.
 */
class ContactForm extends CFormModel
{
	public $name;
	public $email;
	public $subject;
	public $body;
	public $verify_code;

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			// name, email, subject and body are required
			array('name, email, subject, body', 'required'),
			// email has to be a valid email address
			array('email', 'email'),
			// verifyCode needs to be entered correctly
            array('verify_code', 'validateCaptcha'),
		);
	}

    public function validateCaptcha($attribute, $params) {
    		if ($this->getRequireCaptcha())
    			CValidator::createValidator('application.extensions.recaptcha.EReCaptchaValidator',
                                                                            $this, $attribute
                                                                             ,array(  'privateKey'=>Yii::app()->params['recaptcha_private_key']))
                                                                            ->validate($this);
    	}


    public function getRequireCaptcha() {
   	return Yii::app()->params['contactRequireCaptcha'];
   	}

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'verify_code'=>'Verification Code',
		);
	}
}