<?php
/**
 *  PasswordResetForm .php
 *
 * @author: spiros kabasakalis <kabasakalis@gmail.com>
 * Date: 11/15/12
 * Time: 22:46 PM
 */

class PasswordResetForm extends CFormModel {

	public $password;
    public $key;
    public $email;

	/**
	 * Model rules
	 * @return array
	 */
	public function rules() {
		return array(
			array('password', 'required'),
            array('password', 'length', 'max' => User::PASSWORD_MAX, 'min' =>User::PASSWORD_MIN),
		);
	}

	/**
	 * Returns attribute labels
	 * @return array
	 */
	public function attributeLabels() {
		return array(
			'email' => Yii::t('labels', 'Email'),
		);
	}

}
