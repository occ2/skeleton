<?php
namespace occ2\inventar\User\controls\forms;

use occ2\FormControl\IProcessor;
use occ2\FormControl\FormControl;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;
use occ2\inventar\User\presenters\MainPresenter;

/**
 * ExpiredPassFormProcessor
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class ExpiredPassFormProcessor implements IProcessor
{
    public function process(FormControl $form, Presenter $presenter)
    {
        $form->onSuccess[] = function (Form $form) use ($presenter) {
            $values = $form->getValues();
            try {
                $presenter->usersFacade->changeExpiredPassword($values);
                $presenter->flashMessage("user.success.changePassword", MainPresenter::STATUS_SUCCESS, "user.success.changePasswordComment", MainPresenter::$iconPrefix . MainPresenter::ICON_SUCCESS);
                $presenter->redirect(MainPresenter::ACTION_SIGNIN);
            } catch (\Exception $exc) {
                if ($exc instanceof AuthenticationException && $exc->getCode() == AuthenticationException::IDENTITY_NOT_FOUND) {
                    $presenter[MainPresenter::EXPIRED_PASS_FORM]->getItem(ExpiredPassForm::USERNAME)->addError("user.error.invalidUsername");
                    $presenter[MainPresenter::EXPIRED_PASS_FORM]->reload();
                } elseif ($exc instanceof AuthenticationException && $exc->getCode() == AuthenticationException::INVALID_CREDENTIAL) {
                    $presenter[MainPresenter::EXPIRED_PASS_FORM]->getItem(ExpiredPassForm::OLD_PASSWORD)->addError("user.error.invalidPassword");
                    $presenter[MainPresenter::EXPIRED_PASS_FORM]->reload();
                } elseif ($exc instanceof UsersException && $exc->getCode() == UsersException::PASSWORDS_NOT_SAME) {
                    $presenter[MainPresenter::EXPIRED_PASS_FORM]->getItem(ExpiredPassForm::REPEATED_PASSWORD)->addError("user.error.equalPassword");
                    $presenter[MainPresenter::EXPIRED_PASS_FORM]->reload();
                } else {
                    throw $exc;
                }
            }
        };
    }
}