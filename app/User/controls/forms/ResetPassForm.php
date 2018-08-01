<?php
namespace occ2\inventar\User\controls\forms;

use occ2\FormControl\FormControl;

/**
 * ResetPassForm
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 *
 * @title user.resetPassForm.title
 * @comment user.resetPassForm.comment
 * @styles (headerBackground="light",headerText="dark",size="w-100")
 * @rError (container='div class="row"')
 * @rControl (container='div class="col-lg-12 col-md-12 col-sm-12"')
 * @rLabel (requiredsuffix="",container='div class="col-lg-0 col-md-0 col-sm-0"')
 * @ajax
 */
final class ResetPassForm extends FormControl
{
    const USERNAME="username",
          EMAIL="email",
          RECAPTCHA="recaptcha";

    /**
     * @leftAddon user.resetPassForm.username
     * @rightIcon user
     * @type text
     * @cols 20
     * @validator (type=':filled',message='user.error.requiredUsername')
     * @var TextInput
     */
    public $username;
    
    /**
     * @leftAddon user.resetPassForm.email
     * @rightIcon at
     * @type text
     * @cols 20
     * @validator (type=':filled',message='user.error.requiredEmail')
     * @validator (type=':email',message='user.error.invalidEmail')
     * @var TextInput
     */
    public $email;

    /**
     * @label user.resetPassForm.submit
     * @type submit
     * @var SubmitButton
     */
    public $submit;
}
