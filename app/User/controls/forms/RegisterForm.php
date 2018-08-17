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
 * RegisterForm
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 *
 * @title user.registerForm.title
 * @styles (headerBackground="light",headerText="dark",size="w-100")
 * @rError (container='div class="row"')
 * @rControl (container='div class="col-lg-12 col-md-12 col-sm-12"')
 * @rLabel (requiredsuffix="",container='div class="col-lg-0 col-md-0 col-sm-0"')
 * @ajax
 * @onSuccess User.RegisterEvents.onFormSuccess
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
          ANSWER="cAnswer",
          EVENT_SUCCESS="User.RegisterEvents.onFormSuccess";
    
    /**
     * @leftAddon user.registerForm.name.label
     * @rightIcon id-badge
     * @type text
     * @cols 20
     * @validator (type=':filled',message='user.error.name.required')
     * @description user.registerForm.name.description
     */
    public $realname;
    
    /**
     * @leftAddon user.registerForm.username.label
     * @rightIcon user
     * @type text
     * @cols 20
     * @validator (type=':filled',message='user.error.username.required')
     * @validator (type=':minLength',message='user.error.username.minLength',value=4)
     * @description user.registerForm.username.description
     */
    public $username;
    
    /**
     * @leftAddon user.registerForm.password.label
     * @rightIcon key
     * @type password
     * @cols 20
     * @validator (type=':filled',message='user.error.password.required')
     * @validator (type=':minLength',message='user.error.password.minLength',value=8)
     * @validator (type=':pattern',message='user.error.password.pattern',value='.*(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).*')
     * @description user.registerForm.password.description
     */
    public $password;
    
    /**
     * @leftAddon user.registerForm.repeatedPassword.label
     * @rightIcon redo
     * @type password
     * @cols 20
     * @validator (type=':filled',message='user.error.password.required')
     * @validator (type=':minLength',message='user.error.password.minLength',value=8)
     * @validator (type=':equal',message='user.error.password.equal',value='password')
     * @description user.registerForm.repeatedPassword.description
     */
    public $repeatedPassword;
    
    /**
     * @leftAddon user.registerForm.email.label
     * @rightIcon at
     * @type text
     * @cols 20
     * @validator (type=':filled',message='user.error.email.required')
     * @validator (type=':email',message='user.error.email.invalid')
     * @description user.registerForm.email.description
     */
    public $email;
    
    /**
     * @leftAddon user.registerForm.phone.label
     * @rightIcon phone
     * @type text
     * @cols 20
     * @validator (type=':filled',message='user.error.phone.required')
     * @validator (type=':pattern',message='user.error.phone.invalid',value='\+(?:[0-9]?){6,14}[0-9]')
     * @description user.registerForm.phone.description
     */
    public $phone;
    
    /**
     * @leftAddon user.registerForm.question.label
     * @rightIcon question-circle
     * @type text
     * @cols 20
     * @validator (type=':filled',message='user.error.question.required')
     * @description user.registerForm.question.description
     */
    public $cQuestion;
    
    /**
     * @leftAddon user.registerForm.answer.label
     * @rightIcon user-secret
     * @type text
     * @cols 20
     * @validator (type=':filled',message='user.error.answer.required')
     * @description user.registerForm.answer.description
     */
    public $cAnswer;
    
    /**
     * @label user.registerForm.submit.label
     * @type submit
     */
    public $submit;
}
