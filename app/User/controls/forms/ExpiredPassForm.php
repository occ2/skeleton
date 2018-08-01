<?php
namespace occ2\inventar\User\controls\forms;

use occ2\FormControl\FormControl;

/**
 * ExpiredPassForm
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 *
 * @title user.expiredPassForm.title
 * @comment user.expiredPassForm.comment
 * @styles (headerBackground="light",headerText="dark",size="w-100")
 * @rError (container='div class="row"')
 * @rControl (container='div class="col-lg-12 col-md-12 col-sm-12"')
 * @rLabel (requiredsuffix="",container='div class="col-lg-0 col-md-0 col-sm-0"')
 * @ajax
 */
final class ExpiredPassForm extends FormControl
{
    const USERNAME="username",
          OLD_PASSWORD="oldPassword",
          NEW_PASSWORD="newPassword",
          REPEATED_PASSWORD="repeatedPassword";

    /**
     * @leftAddon user.expiredPassForm.username
     * @rightIcon user
     * @type text
     * @cols 20
     * @validator (type=':filled',message='user.error.requiredUsername')
     * @validator (type=':minLength',message='user.error.minLengthUsername',value=4)
     * @var TextInput
     */
    public $username;
    
    /**
     * @leftAddon user.expiredPassForm.oldPassword
     * @rightIcon unlock-alt
     * @type password
     * @cols 20
     * @validator (type=':filled',message='user.error.requiredPassword')
     * @validator (type=':minLength',message='user.error.minLengthPassword',value=8)
     * @description user.expiredPassForm.oldPasswordDescription
     * @var TextInput
     */
    public $oldPassword;
    
    /**
     * @leftAddon user.expiredPassForm.newPassword
     * @rightIcon key
     * @type password
     * @cols 20
     * @validator (type=':filled',message='user.error.requiredPassword')
     * @validator (type=':minLength',message='user.error.minLengthPassword',value=8)
     * @validator (type=':pattern',message='user.error.patternPassword',value='.*(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).*')
     * @description user.expiredPassForm.newPasswordDescription
     * @var TextInput
     */
    public $newPassword;
    
    /**
     * @leftAddon user.expiredPassForm.repeatedPassword
     * @rightIcon redo
     * @type password
     * @cols 20
     * @validator (type=':filled',message='user.error.requiredPassword')
     * @validator (type=':minLength',message='user.error.minLengthPassword',value=8)
     * @validator (type=':equal',message='user.error.equalPassword',value='newPassword')
     * @description user.expiredPassForm.repeatedPasswordDescription
     * @var TextInput
     */
    public $repeatedPassword;
    
    /**
     * @label user.expiredPassForm.submit
     * @type submit
     * @var SubmitButton
     */
    public $submit;
}
