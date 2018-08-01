<?php
namespace occ2\inventar\User\controls\forms;

use occ2\FormControl\FormControl;

/**
 * SettingsForm
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 *
 * @title user.settingsForm.title
 * @styles (headerBackground="light",headerText="dark",size="w-100")
 * @rError (container='div class="row"')
 * @rControl (container='div class="col-lg-12 col-md-12 col-sm-12"')
 * @rLabel (requiredsuffix="",container='div class="col-lg-0 col-md-0 col-sm-0"')
 * @ajax
 */
final class SettingsForm extends FormControl
{
    const ID="id",
          REALNAME="realname",
          USERNAME="username",
          EMAIL="email",
          PHONE="phone",
          QUESTION="cQuestion",
          ANSWER="cAnswer";
    
    /**
     * @type hidden
     * @var HiddenField
     */
    public $id;
    
    /**
     * @leftAddon user.settingsForm.name
     * @rightIcon id-badge
     * @type text
     * @cols 20
     * @validator (type=':filled',message='user.error.requiredName')
     * @description user.settingsForm.nameDescription
     * @var TextInput
     */
    public $realname;
    
    /**
     * @leftAddon user.settingsForm.username
     * @rightIcon user
     * @type text
     * @cols 20
     * @validator (type=':filled',message='user.error.requiredUsername')
     * @validator (type=':minLength',message='user.error.minLengthUsername',value=4)
     * @description user.settingsForm.usernameDescription
     * @var TextInput
     */
    public $username;
    
    /**
     * @leftAddon user.settingsForm.email
     * @rightIcon at
     * @type text
     * @cols 20
     * @validator (type=':filled',message='user.error.requiredEmail')
     * @validator (type=':email',message='user.error.invalidEmail')
     * @description user.settingsForm.emailDescription
     * @var TextInput
     */
    public $email;
    
    /**
     * @leftAddon user.settingsForm.phone
     * @rightIcon phone
     * @type text
     * @cols 20
     * @validator (type=':filled',message='user.error.requiredPhone')
     * @validator (type=':pattern',message='user.error.invalidPhone',value='\+(?:[0-9]?){6,14}[0-9]')
     * @description user.settingsForm.phoneDescription
     * @var TextInput
     */
    public $phone;
    
    /**
     * @leftAddon user.settingsForm.question
     * @rightIcon question-circle
     * @type text
     * @cols 20
     * @validator (type=':filled',message='user.error.requiredQuestion')
     * @description user.settingsForm.questionDescription
     * @var TextInput
     */
    public $cQuestion;
    
    /**
     * @leftAddon user.settingsForm.answer
     * @rightIcon user-secret
     * @type text
     * @cols 20
     * @description user.settingsForm.answerDescription
     * @placeholder user.settingsForm.hidden
     * @var TextInput
     */
    public $cAnswer;
    
    /**
     * @label user.settingsForm.submit
     * @type submit
     * @var SubmitButton
     */
    public $submit;
}
