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
 * ProfileForm
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 *
 * @title user.settingsForm.title
 * @styles (headerBackground="light",headerText="dark",size="w-100")
 * @rError (container='div class="row"')
 * @rControl (container='div class="col-lg-12 col-md-12 col-sm-12"')
 * @rLabel (requiredsuffix="",container='div class="col-lg-0 col-md-0 col-sm-0"')
 * @ajax
 * @onSuccess User.ProfileEvents.onSuccess
 */
final class ProfileForm extends FormControl
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
     */
    public $id;
    
    /**
     * @leftAddon user.settingsForm.name
     * @rightIcon id-badge
     * @type text
     * @cols 20
     * @validator (type=':filled',message='user.error.requiredName')
     * @description user.settingsForm.nameDescription
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
     */
    public $phone;
    
    /**
     * @leftAddon user.settingsForm.question
     * @rightIcon question-circle
     * @type text
     * @cols 20
     * @validator (type=':filled',message='user.error.requiredQuestion')
     * @description user.settingsForm.questionDescription
     */
    public $cQuestion;
    
    /**
     * @leftAddon user.settingsForm.answer
     * @rightIcon user-secret
     * @type text
     * @cols 20
     * @description user.settingsForm.answerDescription
     * @placeholder user.settingsForm.hidden
     */
    public $cAnswer;
    
    /**
     * @label user.settingsForm.submit
     * @type submit
     */
    public $submit;
}
