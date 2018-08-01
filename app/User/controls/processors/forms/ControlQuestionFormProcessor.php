<?php
namespace occ2\inventar\User\controls\forms;

use occ2\FormControl\IProcessor;
use occ2\FormControl\FormControl;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;
use occ2\inventar\User\presenters\MainPresenter;
use occ2\inventar\User\models\exceptions\AuthenticationException;

/**
 * ControlQuestionFormProcessor
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class ControlQuestionFormProcessor implements IProcessor
{
    public function process(FormControl $form, Presenter $presenter)
    {
        $form->setComment($presenter->question);
        $form->onSuccess[]=function (Form $form) use ($presenter) {
            $values = $form->getValues(true);
            $values[ResetPassForm::USERNAME] = $presenter->username;
            $values[ResetPassForm::EMAIL] = $presenter->email;
            try {
                $model = $presenter->user->getAuthenticator();
                $model->processReset($values);
                $presenter->answer = $presenter->resetSession->{ControlQuestionForm::ANSWER} = $values[ControlQuestionForm::ANSWER];
                $presenter->stage = $presenter->resetSession->stage = 2;
                $presenter->redirect(MainPresenter::THIS);
            } catch (AuthenticationException $exc) {
                if ($exc->getCode()==AuthenticationException::INVALID_CONTROL_ANSWER) {
                    $presenter[MainPresenter::CONTROL_QUESTION_FORM]->getItem(ControlQuestionForm::ANSWER)->addError("user.error.invalidAnswer");
                    $presenter[MainPresenter::CONTROL_QUESTION_FORM]->reload();
                } else {
                    throw $exc;
                }
            }
        };
    }
}