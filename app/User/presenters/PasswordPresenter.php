<?php
namespace app\User\presenters;

/**
 * PasswordPresenter
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class PasswordPresenter extends BasePresenter
{
    public function actionDefault()
    {
        $this->forward("expired");
        return;
    }

    public function actionExpired()
    {

    }

    public function actionReset()
    {

    }

    public function actionControlQuestion()
    {

    }

    public function createComponentExpiredPassForm()
    {

    }

    public function createComponentResetPassForm()
    {

    }

    public function createComponentControlQuestionForm()
    {

    }
}