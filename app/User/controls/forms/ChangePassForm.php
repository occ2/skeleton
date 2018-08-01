<?php
namespace occ2\inventar\User\controls\forms;

use occ2\FormControl\FormControl;

/**
 * ChangePassForm
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 *
 * @title user.changePassForm.title
 * @comment user.changePassForm.comment
 * @styles (headerBackground="light",headerText="dark",size="w-100")
 * @rError (container='div class="row"')
 * @rControl (container='div class="col-lg-12 col-md-12 col-sm-12"')
 * @rLabel (requiredsuffix="",container='div class="col-lg-0 col-md-0 col-sm-0"')
 * @ajax
 */
final class ChangePassForm extends FormControl
{
    const ID="id",
          OLD_PASSWORD="oldPassword",
          NEW_PASSWORD="newPassword",
          REPEATED_PASSWORD="repeatedPassword";

    /**
     * @type hidden
     * @var HiddenField
     */
    public $id;
    
    /**
     * @leftAddon user.changePassForm.oldPassword
     * @rightIcon unlock-alt
     * @type password
     * @cols 20
     * @validator (type=':filled',message='user.error.requiredPassword')
     * @validator (type=':minLength',message='user.error.minLengthPassword',value=8)
     * @description user.changePassForm.oldPasswordDescription
     * @var TextInput
     */
    public $oldPassword;
    
    /**
     * @leftAddon user.changePassForm.newPassword
     * @rightIcon key
     * @type password
     * @cols 20
     * @validator (type=':filled',message='user.error.requiredPassword')
     * @validator (type=':minLength',message='user.error.minLengthPassword',value=8)
     * @validator (type=':pattern',message='user.error.patternPassword',value='.*(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).*')
     * @description user.changePassForm.newPasswordDescription
     * @var TextInput
     */
    public $newPassword;
    
    /**
     * @leftAddon user.changePassForm.repeatedPassword
     * @rightIcon redo
     * @type password
     * @cols 20
     * @validator (type=':filled',message='user.error.requiredPassword')
     * @validator (type=':minLength',message='user.error.minLengthPassword',value=8)
     * @validator (type=':equal',message='user.error.equalPassword',value='newPassword')
     * @description user.changePassForm.repeatedPasswordDescription
     * @var TextInput
     */
    public $repeatedPassword;
    
    /**
     * @label user.changePassForm.submit
     * @type submit
     * @var SubmitButton
     */
    public $submit;
}
