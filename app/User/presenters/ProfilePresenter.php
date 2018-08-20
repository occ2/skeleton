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

namespace app\User\presenters;

use app\User\controls\forms\ProfileForm;
use app\User\controls\forms\PasswordForm;

/**
 * ProfilePresenter
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class ProfilePresenter extends BasePresenter
{
    const ACTION_DEFAULT=":User:Profile:default",
          ACTION_SETTINGS=":User:Profile:settings",
          ACTION_HISTORY=":User:Profile:history",
          ACTION_PASSWORD=":User:Profile:password";
    
    /**
     * @inject
     * @var \app\User\controls\factories\IProfileForm
     */
    public $profileFormFactory;

    /**
     * @inject
     * @var \app\User\controls\factories\IPasswordForm
     */
    public $passwordFormFactory;

    public $settingsGridFactory;

    public $historyGridFactory;

    /**
     * @title user.navbar.profile
     */
    public function actionDefault()
    {

    }

    /**
     * @title user.navbar.settings
     */
    public function actionSettings()
    {

    }

    /**
     * @title user.navbar.history
     */
    public function actionHistory()
    {

    }

    /**
     * @title user.navbar.password
     */
    public function actionPassword()
    {

    }

    /**
     * profile form factory
     * @return ProfileForm
     */
    public function createComponentProfileForm()
    {
        // create form
        $form = $this->profileFormFactory->create();

        // fill it with user data
        $data = $this->user->getIdentity()->data;
        unset($data[ProfileForm::ANSWER]);
        $form->setDefaults($data);

        // and return
        return $form;
    }

    /**
     * password form factory
     * @return PasswordForm
     */
    public function createComponentPasswordForm()
    {
        // create form
        $form = $this->passwordFormFactory->create();
        // fill it with user id
        $form->getItem(PasswordForm::ID)
             ->setValue($this->user->getId());
        //and return
        return $form;
    }

    public function createComponentSettingsGrid()
    {
        //return $this->settingsGridFactory->create();
    }

    public function createComponentHistoryGrid()
    {
        //return $this->historyGridFactory->create();
    }
}