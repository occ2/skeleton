<?php
namespace app\User\presenters;

/**
 * SignPresenter
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class SignPresenter extends BasePresenter
{
    public function actionDefault()
    {
        $this->forward("out");
        return;
    }

    public function actionIn()
    {

    }

    public function actionOut()
    {

    }

    public function createComponentSignInForm()
    {

    }

    public function createComponentSignOutDialog()
    {

    }
}