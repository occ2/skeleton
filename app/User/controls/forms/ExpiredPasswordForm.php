<?php
/*
 * The MIT License
 *
 * Copyright 2018 Milan Onderka <milan_onderka@occ2.cz>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace app\User\controls\forms;

use app\Base\controls\FormControl\FormControl;

/**
 * ExpiredPasswordForm
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 *
 * @title user.expiredPasswordForm.title
 * @comment user.expiredPasswordForm.comment
 * @styles (headerBackground="light",headerText="dark",size="w-100")
 * @rError (container='div class="row"')
 * @rControl (container='div class="col-lg-12 col-md-12 col-sm-12"')
 * @rLabel (requiredsuffix="",container='div class="col-lg-0 col-md-0 col-sm-0"')
 * @ajax
 * @onSuccess User.PasswordEvents.onExpiredFormSuccess
 */
final class ExpiredPasswordForm extends FormControl
{
    const USERNAME="username",
          OLD_PASSWORD="oldPassword",
          NEW_PASSWORD="newPassword",
          REPEATED_PASSWORD="repeatedPassword",
          EVENT_SUCCESS="User.PasswordEvents.onExpiredFormSuccess";

    /**
     * @leftAddon user.expiredPasswordForm.username.label
     * @rightIcon user
     * @type text
     * @cols 20
     * @validator (type=':filled',message='user.error.username.required')
     * @validator (type=':minLength',message='user.error.username.minLength',value=4)
     */
    public $username;
    
    /**
     * @leftAddon user.expiredPasswordForm.oldPassword.label
     * @rightIcon unlock-alt
     * @type password
     * @cols 20
     * @validator (type=':filled',message='user.error.password.required')
     * @validator (type=':minLength',message='user.error.password.minLength',value=8)
     * @description user.expiredPasswordForm.oldPassword.description
     */
    public $oldPassword;
    
    /**
     * @leftAddon user.expiredPasswordForm.newPassword.label
     * @rightIcon key
     * @type password
     * @cols 20
     * @validator (type=':filled',message='user.error.password.required')
     * @validator (type=':minLength',message='user.error.password.minLength',value=8)
     * @validator (type=':pattern',message='user.error.password.pattern',value='.*(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).*')
     * @description user.expiredPasswordForm.newPassword.description
     */
    public $newPassword;
    
    /**
     * @leftAddon user.expiredPasswordForm.repeatedPassword.label
     * @rightIcon redo
     * @type password
     * @cols 20
     * @validator (type=':filled',message='user.error.password.required')
     * @validator (type=':minLength',message='user.error.password.minLength',value=8)
     * @validator (type=':equal',message='user.error.password.equal',value='newPassword')
     * @description user.expiredPasswordForm.repeatedPassword.description
     */
    public $repeatedPassword;
    
    /**
     * @label user.expiredPasswordForm.submit.label
     * @type submit
     */
    public $submit;
}
