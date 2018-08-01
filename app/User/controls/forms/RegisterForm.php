<?php
namespace occ2\inventar\User\controls\forms;

use occ2\FormControl\FormControl;

/**
 * RegisterForm
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 *
 * @title user.registerForm.title
 * @styles (headerBackground="light",headerText="dark",size="w-100")
 * @rError (container='div class="row"')
 * @rControl (container='div class="col-lg-12 col-md-12 col-sm-12"')
 * @rLabel (requiredsuffix="",container='div class="col-lg-0 col-md-0 col-sm-0"')
 * @ajax
 */
final class RegisterForm extends FormControl
{
    const
          REALNAME="realname",
          USERNAME="username",
          PASSWORD="password",
          REPEATED_PASSWORD="repeatedPassword",
          EMAIL="email",
          PHONE="phone",
          QUESTION="cQuestion",
          ANSWER="cAnswer";
    
    /**
     * @leftAddon user.registerForm.name
     * @rightIcon id-badge
     * @type text
     * @cols 20
     * @validator (type=':filled',message='user.error.requiredName')
     * @description user.registerForm.nameDescription
     * @var TextInput
     */
    public $realname;
    
    /**
     * @leftAddon user.registerForm.username
     * @rightIcon user
     * @type text
     * @cols 20
     * @validator (type=':filled',message='user.error.requiredUsername')
     * @validator (type=':minLength',message='user.error.minLengthUsername',value=4)
     * @description user.registerForm.usernameDescription
     * @var TextInput
     */
    public $username;
    
    /**
     * @leftAddon user.registerForm.password
     * @rightIcon key
     * @type password
     * @cols 20
     * @validator (type=':filled',message='user.error.requiredPassword')
     * @validator (type=':minLength',message='user.error.minLengthPassword',value=8)
     * @validator (type=':pattern',message='user.error.patternPassword',value='.*(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).*')
     * @description user.registerForm.passwordDescription
     * @var TextInput
     */
    public $password;
    
    /**
     * @leftAddon user.registerForm.repeatedPassword
     * @rightIcon redo
     * @type password
     * @cols 20
     * @validator (type=':filled',message='user.error.requiredPassword')
     * @validator (type=':minLength',message='user.error.minLengthPassword',value=8)
     * @validator (type=':equal',message='user.error.equalPassword',value='password')
     * @description user.registerForm.repeatedPasswordDescription
     * @var TextInput
     */
    public $repeatedPassword;
    
    /**
     * @leftAddon user.registerForm.email
     * @rightIcon at
     * @type text
     * @cols 20
     * @validator (type=':filled',message='user.error.requiredEmail')
     * @validator (type=':email',message='user.error.invalidEmail')
     * @description user.registerForm.emailDescription
     * @var TextInput
     */
    public $email;
    
    /**
     * @leftAddon user.registerForm.phone
     * @rightIcon phone
     * @type text
     * @cols 20
     * @validator (type=':filled',message='user.error.requiredPhone')
     * @validator (type=':pattern',message='user.error.invalidPhone',value='\+(?:[0-9]?){6,14}[0-9]')
     * @description user.registerForm.phoneDescription
     * @var TextInput
     */
    public $phone;
    
    /**
     * @leftAddon user.registerForm.question
     * @rightIcon question-circle
     * @type text
     * @cols 20
     * @validator (type=':filled',message='user.error.requiredQuestion')
     * @description user.registerForm.questionDescription
     * @var TextInput
     */
    public $cQuestion;
    
    /**
     * @leftAddon user.registerForm.answer
     * @rightIcon user-secret
     * @type text
     * @cols 20
     * @validator (type=':filled',message='user.error.requiredAnswer')
     * @description user.registerForm.answerDescription
     * @var TextInput
     */
    public $cAnswer;
    
    /**
     * @type recaptcha
     * @var \Contributte\ReCaptcha\Forms\ReCaptchaField
     */
    public $recaptcha;
    
    /**
     * @label user.registerForm.submit
     * @type submit
     * @var SubmitButton
     */
    public $submit;
}
