<?php
/**
 * EmailForm.php
 *
 * @author: spiros kabasakalis <kabasakalis@gmail.com>
 * Date: 11/15/12
 * Time: 22:46 PM
 */

class EmailForm extends CFormModel {

	public $email;

	/**
	 * Model rules
	 * @return array
	 */
	public function rules() {
		return array(
			array('email', 'required'),
			array('email', 'email'),
			array('email', 'length', 'max' =>User::EMAIL_MAX),
			array('email', 'exist', 'className' => 'User'),
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
