<?php
namespace occ2\inventar\User\controls\forms;

use occ2\FormControl\FormControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Controls\SubmitButton;

/**
 * SignInForm
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 *
 * @title user.signInForm.title
 * @styles (headerBackground="light",headerText="dark",size="w-100")
 * @links (link="register",text="user.signInForm.register")
 * @links (link="reset",text="user.signInForm.forget")
 *
 * @rError (container='div class="row"')
 * @rControl (container='div class="col-lg-12 col-md-12 col-sm-12"')
 * @rLabel (requiredsuffix="",container='div class="col-lg-0 col-md-0 col-sm-0"')
 * @ajax
 * @events
 */
final class SignInForm extends FormControl
{
    const USERNAME="username",
          PASSWORD="password";
    
    /**
     * @leftAddon user.signInForm.username
     * @rightIcon user
     * @type text
     * @cols 30
     * @validator (type=':filled',message='user.error.requiredUsername')
     * @var TextInput
     */
    public $username;
    
    /**
     * @leftAddon user.signInForm.password
     * @rightIcon key
     * @type password
     * @cols 30
     * @validator (type=':filled',message='user.error.requiredPassword')
     * @var TextInput
     */
    public $password;
    
    /**
     * @label user.signInForm.submit
     * @type submit
     * @var SubmitButton
     */
    public $submit;
}
