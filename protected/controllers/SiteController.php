<?php
/**
 * SiteController.php
 *
 * @author: spiros kabasakalis <kabasakalis@gmail.com>
 * Date: 11/15/12
 * Time: 22:46 PM
 */

class SiteController extends Controller
{


    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return array(
            // captcha action renders the CAPTCHA image displayed on the contact page
            'captcha' => array(
                'class' => 'CCaptchaAction',
                'backColor' => 0xFFFFFF,
            ),
            // page action renders "static" pages stored under 'protected/views/site/pages'
            // They can be accessed via: index.php?r=site/page&view=FileName
            'page' => array(
                'class' => 'CViewAction',
            ),
        );
    }

    /**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested by users.
     */
    public function actionIndex()
    {
        // renders the view file 'protected/views/site/index.php'
        // using the default layout 'protected/views/layouts/main.php'
        $this->render('index');

    }


    /**
     * This is the action to handle external exceptions.
     */
    public function actionError()
    {
        if ($error = Yii::app()->errorHandler->error) {
            if (Yii::app()->request->isAjaxRequest)
                echo $error['message'];
            else
                $this->render('error', $error);
        }
    }

    /**
     * Displays the contact page
     */
    public function actionContact()
    {
        $model = new ContactForm;
        if (isset($_POST['ContactForm'])) {
            $model->attributes = $_POST['ContactForm'];
            if ($model->validate()) {
                sendHtmlEmail(
                    app()->params['myEmail'],
                    $model->name,
                    $model->email,
                    $model->subject,
                    array('body' => $model->body,
                                'name' => $model->name,
                                'subject' => $model->subject,
                               'email' => $model->email),
                  'contact',
                  'main3'
                );
                Yii::app()->user->setFlash('contact', '<strong>Message sent!   </strong>Thank you for contacting us. We will respond to you as soon as possible.');
                $this->refresh();
            }
        }
        $this->render('contact', array('model' => $model));
    }

    public function actionRegister()
    {
        $model = new RegisterForm();

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'register-form') {
            echo CActiveForm::validate($model, array('username', 'password'));
            Yii::app()->end();
        }

        if (isset($_POST['RegisterForm'])) {
            $model->attributes = $_POST['RegisterForm'];
            if ($model->validate(array('email', 'username', 'new_password', 'password_confirm'))) {
                $user = new User();
                $user->email = $_POST['RegisterForm']['email'];
                $user->username = $_POST['RegisterForm']['username'];
                $user->password = $_POST['RegisterForm']['new_password'];

                if ($user->save()) {
                    //send email         activation key has been generated on beforeValidate function in User class
                    $activation_url = $this->createAbsoluteUrl('/site/activate', array('key' => $user->activation_key, 'email' => $user->email));

                    if (sendHtmlEmail(
                        $user->email,
                        Yii::app()->name . ' Administrator',
                        null,
                        Yii::t('register', 'Account activation'),
                        array('username' => $user->username, 'activation_url' => $activation_url),
                        'activation',
                        'main2'
                    )
                    ) {
                        $msg = Yii::t('register', 'Please check your email inbox for the activation link.It is valid for 24 hours.');
                        Yii::app()->user->setFlash('success', $msg);
                        $this->redirect(bu() . '/site/login');
                    } else {
                        $user->delete();
                        $msg = Yii::t('register', 'Error.Activation email could not be sent.Please register again.');
                        Yii::app()->user->setFlash('error', $msg);
                        $this->redirect(bu() . '/site/register');
                    }
                }
            }
        }

        $this->render('register', array('model' => $model));
    }

    public function actionEmail_for_reset()
    {

        if (isset($_POST['EmailForm'])) {
            $user_email = $_POST['EmailForm']['email'];
            $criteria = new CDbCriteria;
            $criteria->condition = 'email=:email';
            $criteria->params = array(':email' => $user_email);
            $user = User::model()->find($criteria);
            if (!$user) {
                $errormsg = Yii::t('passwordreset', 'No user with this email in our records');
                Yii::app()->user->setFlash('error', $errormsg);
                $this->refresh();
            }
            $key = $user->generate_activation_key();
            $user->reset_token = $key;
            $reset_url = $this->createAbsoluteUrl('/site/password_reset', array('key' => $key, 'email' => $user_email));
            $user->save();

            if (sendHtmlEmail(
                $user->email,
                Yii::app()->name . ' Administrator',
                null,
                Yii::t('reset', 'Password reset.'),
                array('username' => $user->username, 'reset_url' => $reset_url),
                'pwd_reset',
                'main'
            )
            ) {
                $infomsg = Yii::t('passwordreset', 'We have sent you a reset link,please check your email inbox.');
                Yii::app()->user->setFlash('info', $infomsg);
                $this->refresh();
            } else {
                $errormsg = Yii::t('passwordreset', 'We could not email you the password reset link');
                Yii::app()->user->setFlash('info', $errormsg);
                $this->refresh();
            }
        }

        $model = new EmailForm;
        $this->render('email_for_reset', array('model' => $model));
    }

    public function actionPassword_reset($key, $email)
    {

        if (isset($_POST['PasswordResetForm'])) {
            $new_password = $_POST['PasswordResetForm']['password'];
            $key = $_POST['PasswordResetForm']['key'];
            $email = $_POST['PasswordResetForm']['email'];


            $criteria = new CDbCriteria;
            $criteria->condition = 'reset_token=:reset_token AND email=:email';
            $criteria->params = array(':reset_token' => $key, ':email' => $email);
            $user = User::model()->find($criteria);

            if (!$user) {
                $errormsg = Yii::t('passwordreset', 'Error,your account information was not found.
                Your reset token has probably been used or  expired.Please repeat the password reset process.');
                Yii::app()->user->setFlash('error', $errormsg);
                $this->refresh();
            }
            $user->password = $new_password;
            $user->reset_token = NULL;

            if ($user->save()) {
                $msg = Yii::t('passwordreset', 'Your password has been reset.Log in with your new password.');
                Yii::app()->user->setFlash('success', $msg);
                $this->redirect(bu() . '/site/login');
            } else {
                $error = Yii::t('passwordreset', 'Error,could not reset your password.');
                Yii::app()->user->setFlash('error', $error);
                $this->refresh();
            }
        }

        $model = new PasswordResetForm;
        $this->render('password_reset', array('model' => $model, 'key' => $key, 'email' => $email));
    }


    public function actionActivate($key, $email)
    {
        $criteria = new CDbCriteria;
        $criteria->condition = 'activation_key=:activation_key AND email=:email';
        $criteria->params = array(':activation_key' => $key, ':email' => $email);
        $user = User::model()->find($criteria);
        if ($user) {
            $user->activation_key = NULL;
            $user->status = User::STATUS_ACTIVE;
            $user->save(false); //user has already  been validated when saved for the forst time.
            $successmsg = Yii::t('registration', ',welcome! Your account has been activated.Now you can log in.');
            Yii::app()->user->setFlash('success', $user->username . $successmsg);
            $this->redirect(bu() . '/site/login');
        } else {
            $errormsg = Yii::t('registration', ' Error.Your account could not be activated,please repeat the registration process.');
            $criteria = new CDbCriteria;
            $criteria->condition = ' email=:email';
            $criteria->params = array(':email' => $email);
            $user = User::model()->find($criteria);
            $user->delete();
            Yii::app()->user->setFlash('error', $errormsg);
            $this->redirect(bu() . '/site/register');
        }
    }


    public function actionLogin()
    {

        $model = new LoginForm();

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'login-form') {
            echo CActiveForm::validate($model, array('username', 'password', 'verify_code'));
            Yii::app()->end();
        }

        if (isset($_POST['LoginForm'])) {
            $model->attributes = $_POST['LoginForm'];
            if ($model->validate(array('username', 'password', 'verify_code')) && $model->login()) {
                Yii::app()->user->setFlash('success', 'Welcome ' . app()->user->name);
                $this->redirect(Yii::app()->user->returnUrl);
            }
        }

        $this->render('login', array('model' => $model));

    }

    /**
     * This is the action that handles user's logout
     */
    public function actionLogout()
    {
        Yii::app()->user->logout();
        $this->redirect(Yii::app()->homeUrl);
    }


    //test email with Gmail SMPT server
        public function actionGmail()
        {

            $mailer = Yii::createComponent('application.extensions.mailer.EMailer');

            // these settings only for server,I use other settings in my localhost
            if (APP_DEPLOYED) {
                $mailer->Host = 'smtp.gmail.com';
                $mailer->IsSMTP();
                $mailer->SMTPAuth = true;
                $mailer->SMTPSecure = 'tls';
                $mailer->Port = '587';
                $mailer->Username =app()->params['myEmail'];
                $mailer->Password =  app()->params['gmail_password'];
            }

            $mailer->From = app()->params['fromEmail'];
            $mailer->AddReplyTo( app()->params['replyEmail']);
            $mailer->AddAddress(app()->params['myEmail']);
            $mailer->FromName = 'Me-Testing';

            $mailer->CharSet = 'UTF-8';
            $mailer->Subject = Yii::t('demo', 'It Works');
            $message = 'Done!';
            $mailer->Body = $message;
            $mailer->SMTPDebug = true;
            fb($mailer, "mailer OBJECT");
            $mailer->Send();
        }


}