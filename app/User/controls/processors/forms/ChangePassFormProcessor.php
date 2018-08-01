<?php
namespace occ2\inventar\User\controls\forms;

use occ2\FormControl\IProcessor;
use occ2\FormControl\FormControl;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;
use occ2\inventar\User\presenters\ProfilePresenter;
use occ2\inventar\User\models\exceptions\AuthenticationException;

/**
 * ChangePassFormProcessor
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class ChangePassFormProcessor implements IProcessor
{
    public function process(FormControl $form, Presenter $presenter)
    {
        $form->onSuccess[]=function (Form $form) use ($presenter) {
            $values = $form->getValues();
            try {
                $presenter->usersFacade->changePassword($values);
                $presenter[ProfilePresenter::CHANGE_PASS_FORM]->flashMessage("user.success.changePass", ProfilePresenter::STATUS_SUCCESS, "", ProfilePresenter::$iconPrefix . ProfilePresenter::ICON_SUCCESS, 100);
                if ($presenter->isAjax()) {
                    $presenter[ProfilePresenter::CHANGE_PASS_FORM]->redrawControl();
                } else {
                    $presenter->redirect(ProfilePresenter::THIS);
                }
                return;
            } catch (\Exception $exc) {
                if ($exc instanceof AuthenticationException && $exc->getCode()== AuthenticationException::IDENTITY_NOT_FOUND) {
                    $presenter[ProfilePresenter::CHANGE_PASS_FORM]->flashMessage("user.error.invalidUsername", ProfilePresenter::STATUS_DANGER, "", ProfilePresenter::$iconPrefix . ProfilePresenter::ICON_DANGER, 100);
                    $presenter[ProfilePresenter::CHANGE_PASS_FORM]->reload();
                } elseif ($exc instanceof AuthenticationException && $exc->getCode()== AuthenticationException::INVALID_CREDENTIAL) {
                    $presenter[ProfilePresenter::CHANGE_PASS_FORM]->getItem(ChangePassForm::OLD_PASSWORD)->addError("user.error.invalidPassword");
                    $presenter[ProfilePresenter::CHANGE_PASS_FORM]->reload();
                } elseif ($exc instanceof ManagerException && $exc->getCode()== ManagerException::NON_ACCESSABLE_USER) {
                    $presenter[ProfilePresenter::CHANGE_PASS_FORM]->flashMessage("user.error.unaccessible", ProfilePresenter::STATUS_DANGER, "", ProfilePresenter::$iconPrefix . ProfilePresenter::ICON_DANGER, 100);
                    $presenter[ProfilePresenter::CHANGE_PASS_FORM]->reload();
                } elseif ($exc instanceof ManagerException && $exc->getCode()== ManagerException::PASSWORDS_NOT_SAME) {
                    $presenter[ProfilePresenter::CHANGE_PASS_FORM]->getItem(ChangePassForm::REPEATED_PASSWORD)->addError("user.error.equalPassword");
                    $presenter[ProfilePresenter::CHANGE_PASS_FORM]->reload();
                } else {
                    throw $exc;
                }
            }
        };
    }
}