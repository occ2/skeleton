<?php
namespace occ2\inventar\User\controls\forms;

use occ2\FormControl\IProcessor;
use occ2\FormControl\FormControl;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;
use occ2\inventar\User\presenters\ProfilePresenter;

/**
 *SettingsFormProcessor
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class SettingsFormProcessor implements IProcessor
{
    public function process(FormControl $form, Presenter $presenter)
    {
        $form->onSuccess[] = function (Form $form) use ($presenter) {
            $values = $form->getValues();
            try {
                $presenter->usersFacade->saveSettings($values);
                $presenter[ProfilePresenter::SETTINGS_FORM]->flashMessage("user.success.saveSettings", ProfilePresenter::STATUS_SUCCESS, "", ProfilePresenter::$iconPrefix . ProfilePresenter::ICON_SUCCESS, 100);
                if ($presenter->isAjax()) {
                    $presenter[ProfilePresenter::SETTINGS_FORM]->redrawControl();
                } else {
                    $presenter->redirect(ProfilePresenter::THIS);
                }
                return;
            } catch (UsersException $exc) {
                if ($exc->getCode()== UsersException::USERNAME_NOT_UNIQUE) {
                    $presenter[ProfilePresenter::SETTINGS_FORM]->getItem(SettingsForm::USERNAME)->addError("user.error.uniqueUsername");
                    $presenter[ProfilePresenter::SETTINGS_FORM]->reload();
                } elseif ($exc->getCode()== UsersException::NON_ACCESSABLE_USER) {
                    $presenter[ProfilePresenter::SETTINGS_FORM]->flashMessage("user.error.unaccessible", ProfilePresenter::STATUS_DANGER, "", ProfilePresenter::$iconPrefix . ProfilePresenter::ICON_DANGER, 100);
                    $presenter[ProfilePresenter::SETTINGS_FORM]->reload();
                } else {
                    throw $exc;
                }
            }
        };
    }
}