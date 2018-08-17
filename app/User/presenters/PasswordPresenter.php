<?php
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