<?php
namespace occ2\inventar\User\controls\forms;

use occ2\FormControl\IProcessor;
use occ2\FormControl\FormControl;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;
use occ2\inventar\User\presenters\MainPresenter;

/**
 * ControlRecaptchaFormProcessor
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class ControlRecaptchaFormProcessor implements IProcessor
{
    public function process(FormControl $form, Presenter $presenter)
    {
        $form->onSuccess[] = function (Form $form) use ($presenter) {
            $values = $form->getValues(true);
            $values[ResetPassForm::USERNAME] = $presenter->username;
            $values[ResetPassForm::EMAIL] = $presenter->email;
            $values[ControlQuestionForm::ANSWER] = $presenter->answer;
            try {
                $model = $presenter->user->getAuthenticator();
                $model->processReset($values, true);
                $presenter->username = $presenter->resetSession->{ResetPassForm::USERNAME}=null;
                $presenter->email = $presenter->resetSession->{ResetPassForm::EMAIL}=null;
                $presenter->question = $presenter->resetSession->question=null;
                $presenter->answer = $presenter->resetSession->{ControlQuestionForm::ANSWER}=null;
                $presenter->stage = $presenter->resetSession->stage=null;
                $presenter->flashMessage("user.success.reset", MainPresenter::STATUS_SUCCESS, "user.success.resetComment", MainPresenter::$iconPrefix . MainPresenter::ICON_SUCCESS);
                $presenter->redirect(MainPresenter::ACTION_SIGNIN);
            } catch (\Exception $exc) {
                throw $exc;
            }
        };
    }
}