<?php
namespace occ2\inventar\User\controls\forms;

use occ2\FormControl\IProcessor;
use occ2\FormControl\FormControl;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;
use occ2\inventar\User\presenters\MainPresenter;
use occ2\inventar\User\models\exceptions\AuthenticationException;

/**
 * ResetPassFormProcessor
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class ResetPassFormProcessor implements IProcessor
{
    public function process(FormControl $form, Presenter $presenter)
    {
        $form->onSuccess[] = function (Form $form) use ($presenter) {
            $values = $form->getValues(true);
            try {
                $model = $presenter->user->getAuthenticator();
                $presenter->question = $presenter->resetSession->question = $model->testReset($values);
                $presenter->username = $presenter->resetSession->{ResetPassForm::USERNAME} = $values[ResetPassForm::USERNAME];
                $presenter->email = $presenter->resetSession->{ResetPassForm::EMAIL} = $values[ResetPassForm::EMAIL];
                $presenter->stage = $presenter->resetSession->stage = 1;
                if ($presenter->isAjax()) {
                    $presenter->redrawControl();
                } else {
                    $presenter->redirect(MainPresenter::THIS);
                }
            } catch (AuthenticationException $exc) {
                if ($exc->getCode()== AuthenticationException::IDENTITY_NOT_FOUND) {
                    $presenter[MainPresenter::RESET_PASS_FORM]->getItem(ResetPassForm::USERNAME)->addError("user.error.invalidUsername");
                    $presenter[MainPresenter::RESET_PASS_FORM]->reload();
                } elseif ($exc->getCode()== AuthenticationException::INVALID_EMAIL) {
                    $presenter[MainPresenter::RESET_PASS_FORM]->getItem(ResetPassForm::EMAIL)->addError("user.error.invalidEmail");
                    $presenter[MainPresenter::RESET_PASS_FORM]->reload();
                } else {
                    throw $exc;
                }
            }
        };
    }
}