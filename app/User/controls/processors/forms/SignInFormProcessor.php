<?php
namespace occ2\inventar\User\controls\forms;

use occ2\FormControl\IProcessor;
use occ2\FormControl\FormControl;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;
use occ2\inventar\User\controls\forms\SignInForm;
use occ2\inventar\User\presenters\MainPresenter;
use occ2\inventar\User\models\exceptions\AuthenticationException;

/**
 * SignInFormProcessor
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class SignInFormProcessor implements IProcessor
{
    public function process(FormControl $form, Presenter $presenter){
        $form->onSuccess[] = function (Form $form) use ($presenter) {
            try {
                $values = $form->getValues(true);
                $presenter->user->login($values[SignInForm::USERNAME], $values[SignInForm::PASSWORD]);
                $presenter->restoreRequest($presenter->backlink);
                $presenter->redirect(MainPresenter::ACTION_DEFAULT);
            } catch (AuthenticationException $exc) {
                $signInForm = $presenter[MainPresenter::SIGN_IN_FORM];
                $resetPassForm = $presenter[MainPresenter::RESET_PASS_FORM];
                $expiredPassForm = $presenter[MainPresenter::EXPIRED_PASS_FORM];
                switch ($exc->getCode()) {
                    case 1:
                        $signInForm->getItem(SignInForm::USERNAME)->addError("user.error.invalidUsername");
                        $signInForm->reload();
                        break;
                    case 2:
                        $signInForm->getItem(SignInForm::PASSWORD)->addError("user.error.invalidPassword");
                        $signInForm->reload();
                        break;
                    case 3:
                        $resetPassForm->flashMessage("user.error.maxAttemptsReached", MainPresenter::STATUS_WARNING, "", MainPresenter::$iconPrefix . MainPresenter::ICON_WARNING, 100);
                        $presenter->redirect(MainPresenter::ACTION_RESET);
                        break;
                    case 4:
                        $signInForm->form->addError("user.error.blockedUser");
                        $signInForm->reload();
                        break;
                    case 5:
                        $expiredPassForm->flashMessage("user.error.expiredPassword", MainPresenter::STATUS_INFO, "", MainPresenter::$iconPrefix . MainPresenter::ICON_INFO, 100);
                        $presenter->redirect(MainPresenter::ACTION_EXPIRED);
                        break;
                    default:
                        throw $exc;
                }
            }
        };
    }
}