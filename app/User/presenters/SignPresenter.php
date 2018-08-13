<?php
namespace app\User\presenters;

use app\Base\presenters\AbstractPresenter;
use app\User\controls\forms\SignInForm;

/**
 * SignPresenter
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class SignPresenter extends AbstractPresenter
{
    const ACTION_DEFAULT=":User:Sign:default",
          ACTION_IN=":User:Sign:in",
          ACTION_OUT=":User:Sign:out",

          MESSEGE_LOGOUT="user.success.logout.title";

    /**
     * @inject @var \app\User\controls\factories\ISignInForm
     */
    public $signInFormFactory;

    /**
     * default action
     * @return void
     */
    public function actionDefault()
    {
        $this->forward("out");
        return;
    }

    /**
     * sign in action
     * @title user.signInForm.title
     */
    public function actionIn()
    {}

    /**
     * sign out action
     * @ACL (loggedIn=true)
     * @title user.navbar.signOut
     */
    public function actionOut()
    {
        $this->user->logout(true);
        $this->flashMessage(
            static::MESSEGE_LOGOUT,
            static::STATUS_INFO,
            null,
            static::ICON_INFO
        );
        $this->redirect(static::ACTION_IN);
        return;
    }

    /**
     * sign in form factory
     * @return SignInForm
     */
    public function createComponentSignInForm()
    {
        return $this->signInFormFactory->create();
    }

    /**
     * @todo
     */
    public function createComponentSignOutDialog()
    {

    }
}