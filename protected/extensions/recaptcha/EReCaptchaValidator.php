<?php
/**
 * EReCaptchaValidator class file.
 *
 * @author MetaYii
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 MetaYii
 * @license
 *
 * Copyright © 2008 by MetaYii
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 * - Neither the name of MetaYii nor the names of its contributors may
 *   be used to endorse or promote products derived from this software without
 *   specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 * GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
 * EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

/**
 * Include the reCAPTCHA PHP wrapper.
 */

require_once(Yii::getPathOfAlias('application.extensions.recaptcha').DIRECTORY_SEPARATOR.'reCAPTCHA'.DIRECTORY_SEPARATOR.'recaptchalib.php');

/**
 * EReCaptchaValidator validates that the attribute value is the same as the verification code displayed in the CAPTCHA.
 * The CAPTCHA is provided by reCAPTCHA {@link http://recaptcha.net/}. See LICENCE.txt for the terms of use for this service.
 *
 * EReCaptchaValidator should be used together with {@link EReCaptcha}.
 *
 * @author MetaYii
 * @package application.extensions.recaptcha
 * @since 1.1
 */
class EReCaptchaValidator extends CValidator
{
   /**
    * The private key for reCAPTCHA
    *
    * @var string
    */
   private $privateKey='';

   /**
    * Sets the private key.
    *
    * @param string $value
    * @throws CException if $value is not valid.
    */
   public function setPrivateKey($value)
   {
      if (empty($value)||!is_string($value)) throw new CException(Yii::t('yii','EReCaptchaValidator.privateKey must contain your reCAPTCHA private key.'));
      $this->privateKey = $value;
   }

   /**
    * Returns the reCAPTCHA private key
    *
    * @return string
    */
   public function getPrivateKey()
   {
      return $this->privateKey;
   }

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel the object being validated
	 * @param string the attribute being validated
	 */
	protected function validateAttribute($object,$attribute)
	{
	   $resp = recaptcha_check_answer($this->privateKey,
	                                  $_SERVER['REMOTE_ADDR'],
	                                  $_POST['recaptcha_challenge_field'],
	                                  $_POST['recaptcha_response_field']);
		if (!$resp->is_valid) {
			$message=$this->message!==null?$this->message:Yii::t('yii','The verification code is incorrect.');
			$this->addError($object,$attribute,$message);
		}
	}
}