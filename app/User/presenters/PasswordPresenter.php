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

use app\Base\presenters\AbstractPresenter;
use app\User\controls\forms\ResetPasswordForm;
use app\User\controls\forms\ExpiredPasswordForm;
use app\User\controls\forms\ControlQuestionForm;
use Nette\Http\SessionSection;

/**
 * PasswordPresenter
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class PasswordPresenter extends AbstractPresenter
{
    const ACTION_DEFAULT=":User:Password:default",
          ACTION_EXPIRED=":User:Password:expired",
          ACTION_RESET=":User:Password:reset",
          ACTION_CONTROL_QUESTION=":User:Password:controlQuestion",
          RESET_PASSWORD_SESSION="resetPassword";

    /**
     * @inject @var \app\User\controls\factories\IResetPasswordForm
     */
    public $resetPasswordFormFactory;

    /**
     * @inject @var \app\User\controls\factories\IControlQuestionForm
     */
    public $controlQuestionFormFactory;

    /**
     * @inject @var \app\User\controls\factories\IExpiredPasswordForm
     */
    public $expiredPasswordFormFactory;

    /**
     * @var SessionSection
     */
    private $resetData;

    /**
     * redirectr to expired password
     * @return void
     */
    public function actionDefault()
    {
        $this->forward("expired");
        return;
    }

    /**
     * show form for change expired password
     * @title user.expiredPassForm.title
     */
    public function actionExpired()
    {}

    /**
     * show reset password form
     * @title user.resetPassForm.title
     */
    public function actionReset()
    {}

    /**
     * show control question form
     * load data from session and fill the form
     * @title user.controlQuestionForm.title
     */
    public function actionControlQuestion()
    {
        if($this->session->hasSection(self::RESET_PASSWORD_SESSION)){
            $this->resetData = $this->session->getSection(self::RESET_PASSWORD_SESSION);
        } else {
            $this->redirect(self::ACTION_RESET);
        }
        return;
    }

    /**
     * clear reset process handler
     * @return void
     */
    public function handleClearReset()
    {
        $this->session->getSection(self::RESET_PASSWORD_SESSION)->remove();
        $this->redirect(self::ACTION_RESET);
        return;
    }

    /**
     * factory for ExpiredPasswordForm
     * @return ExpiredPasswordForm
     */
    public function createComponentExpiredPasswordForm()
    {
        return $this->expiredPasswordFormFactory->create();
    }

    /**
     * factory for ResetPasswordForm
     * @return ResetPasswordForm
     */
    public function createComponentResetPasswordForm()
    {
        return $this->resetPasswordFormFactory->create();
    }

    /**
     * factory for ControlQuestionForm
     * @return ControlQuestionForm
     */
    public function createComponentControlQuestionForm()
    {
        $form = $this->controlQuestionFormFactory->create();
        $form->setComment($this->resetData->cQuestion);
        return $form;
    }
}