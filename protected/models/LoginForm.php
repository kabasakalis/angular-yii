<?php
/**
 * LoginForm.php
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 *  * Date: 7/22/12
  * Time: 8:37 PM
 *  @author: spiros kabasakalis <kabasakalis@gmail.com>
 * Date: 11/15/12
 * Time: 22:46 PM
 */


class LoginForm extends CFormModel {


	// maximum number of login attempts before display captcha
	const MAX_LOGIN_ATTEMPTS = 100;

	public $username;
	public $password;
	public $email;
	public $rememberMe;
	public $verify_code;
    public $login_ip;
    public $login_time;
	private $_identity;
	private $_user = null;

	/**
	 * Model rules
	 * @return array
	 */
	public function rules() {
		return array(
			array('username, password', 'required'),
			array('username', 'length', 'max' => User::USERNAME_MAX,'min'=>User::USERNAME_MIN),
			array('password', 'length', 'max' => User::PASSWORD_MAX, 'min' => User::PASSWORD_MIN),
			array('password', 'authenticate'),
			array('rememberMe', 'boolean'),
           array('verify_code', 'validateCaptcha'),
		);
	}

	/**
	 * Returns attribute labels
	 * @return array
	 */
	public function attributeLabels() {
		return array(
			'username' => Yii::t('labels', 'Username or e-mail'),
			'rememberMe' => Yii::t('labels', 'Remember me next time'),
		);
	}

	/**
	 * Authenticates user input against DB
	 * @param $attribute
	 * @param $params
	 */
	public function authenticate($attribute, $params) {
		if (!$this->hasErrors()) {
			$this->_identity = new UserIdentity($this->username, $this->password);

			if (!$this->_identity->authenticate()) {
               $errorCode= $this->_identity->errorCode;

				if (($user = $this->user) !== null && $user->login_attempts < 100)
					$user->saveAttributes(array('login_attempts' => $user->login_attempts + 1));

                    if ($errorCode== UserIdentity::ERROR_INACTIVE){
                      $this->addError('status', Yii::t('errors', 'Your account is  inactive.'));
                    }
                if (($errorCode== UserIdentity::ERROR_PASSWORD_INVALID) ||
                    ($errorCode==UserIdentity::ERROR_USERNAME_INVALID)) {
				$this->addError('username', Yii::t('errors', 'Incorrect username and/or password.'));
				$this->addError('password', Yii::t('errors', 'Incorrect username and/or password.'));
                }

			}
		}
	}


	/**
	 * Validates captcha code
	 * @param $attribute
	 * @param $params
	 */
	public function validateCaptcha($attribute, $params) {
		if ($this->getRequireCaptcha())
			CValidator::createValidator('application.extensions.recaptcha.EReCaptchaValidator',
                                                                        $this, $attribute
                                                                         ,array(  'privateKey'=>Yii::app()->params['recaptcha_private_key']))
                                                                        ->validate($this);
	}

	/**
	 * Login
	 * @return bool
	 */
	public function login() {
		if ($this->_identity === null) {
			$this->_identity = new UserIdentity($this->username, $this->password);
			$this->_identity->authenticate();
		}
		if ($this->_identity->errorCode === UserIdentity::ERROR_NONE) {
			$duration = $this->rememberMe ? 3600 * 24 * 30 : 0; // 30 days
            if (($user = $this->user) !== null )
                  $user->saveAttributes(array('login_time' => $user->login_time + 1,'login_ip'=>getUserIP(),'login_attempts'=>NULL));
			Yii::app()->user->login($this->_identity, $duration);
			return true;
		}
	}

	/**
	 * Returns
	 * @return null
	 */
	public function getUser() {
		if ($this->_user === null) {
			$attribute = strpos($this->username, '@') ? 'email' : 'username';
			$this->_user = User::model()->find(array('condition' => $attribute . '=:loginname', 'params' => array(':loginname' => $this->username)));
		}
		return $this->_user;
	}

	/**
	 * Returns whether it requires captcha or not
	 * @return bool
	 */
	public function getRequireCaptcha() {
		return ($user = $this->user) !== null && $user->login_attempts >= self::MAX_LOGIN_ATTEMPTS;
	}

}
