<?php
namespace occ2\inventar\User\controls\forms;

use occ2\FormControl\FormControl;

/**
 * UserAddForm
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
final class UserForm extends FormControl
{
    const ID="id",
          REALNAME="realname",
          USERNAME="username",
          EMAIL="email",
          PHONE="phone";
    
    /**
     * @type hidden
     */
    public $id;

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
     * @label user.registerForm.submit
     * @type submit
     * @var SubmitButton
     */
    public $submit;
}
