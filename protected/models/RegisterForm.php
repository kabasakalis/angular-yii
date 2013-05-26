<?php
/**
 * RegisterForm.php
 *
 * @author: spiros kabasakalis <kabasakalis@gmail.com>
 * Date: 11/15/12
 * Time: 22:46 PM
 */
class RegisterForm extends CFormModel
{

    public $username;
    public $email;
    public $new_password;
    public $password_confirm;
    public $verify_code;


    public function rules()
    {
        return array(
            array('email,new_password,password_confirm,username', 'required'),
            array('username', 'match', 'allowEmpty' => false, 'pattern' => '/[A-Za-z0-9\x80-\xFF]+$/'),
            array('email', 'email'),
            array('email', 'length', 'min' => User::EMAIL_MIN, 'max' => User::EMAIL_MAX),
            array('new_password,password_confirm', 'length', 'min' => User::PASSWORD_MIN, 'max' => User::USERNAME_MAX),
            array('username,email', 'unique', 'className' => 'User', 'skipOnError' => false),
            array('password_confirm', 'compare', 'compareAttribute' => 'new_password'),
           /*array('verify_code',
                                         'application.extensions.recaptcha.EReCaptchaValidator',
                                         'privateKey'=>Yii::app()->params['recaptcha_private_key']),
           array('verify_code', 'captcha'),*/
        );
    }


    public function attributeLabels()
    {
        return array(
            'username' => Yii::t('app', 'Username'),
            'email' => Yii::t('app', 'Email'),
            'new_password' => Yii::t('app', 'Password'),
            'password_confirm' => Yii::t('app', 'Password Confirmation'),
            'verify_code' => Yii::t('app', 'Captcha'),
        );
    }
}