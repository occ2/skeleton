<?php
namespace app\User\presenters;

use app\Base\presenters\AbstractPresenter;
use app\User\controls\forms\RegisterForm;

/**
 * RegisterPresenter
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class RegisterPresenter extends AbstractPresenter
{
    /**
     * @inject
     * @var \app\User\controls\factories\IRegisterForm
     */
    public $registerFormFactory;

    /**
     * @title user.registerForm.title
     */
    public function actionDefault()
    {}

    /**
     * @return RegisterForm
     */
    public function createComponentRegisterForm()
    {
        return $this->registerFormFactory->create();
    }
}