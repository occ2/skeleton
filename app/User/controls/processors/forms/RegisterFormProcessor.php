<?php
namespace occ2\inventar\User\controls\forms;

use occ2\FormControl\IProcessor;
use occ2\FormControl\FormControl;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;
use occ2\inventar\User\presenters\MainPresenter;

/**
 * RegisterFormProcessor
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class RegisterFormProcessor implements IProcessor
{
    public function process(FormControl $form, Presenter $presenter){
        $form->onSuccess[] = function (Form $form) use ($presenter) {
            try {
                $presenter->usersFacade->registerUser($form->getValues());
                $presenter->flashMessage("user.success.register", MainPresenter::STATUS_SUCCESS, "user.success.registerComment", MainPresenter::$iconPrefix . MainPresenter::ICON_SUCCESS);
                $presenter->redirect(MainPresenter::ACTION_SIGNIN);
            } catch (UsersException $exc) {
                if ($exc->getCode()==UsersException::USERNAME_NOT_UNIQUE) {
                    $presenter[MainPresenter::REGISTER_FORM]->getItem(RegisterForm::REALNAME)->addError("user.error.uniqueUsername");
                    $presenter[MainPresenter::REGISTER_FORM]->reload();
                } else {
                    throw $exc;
                }
            }
        };
    }
}