<?php
/*
 * The MIT License
 *
 * Copyright 2018 Milan Onderka <milan_onderka@occ2.cz>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace app\User\events\subscribers;

use Contributte\EventDispatcher\EventSubscriber;
use app\Base\controls\FormControl\events\FormEvent;
use app\User\models\exceptions\AuthenticationException;
use app\User\models\exceptions\ValidationException;
use app\User\models\exceptions\ProfileException;
use app\User\models\exceptions\PermissionException;
use app\User\controls\forms\ResetPasswordForm;
use app\User\controls\forms\ControlQuestionForm;
use app\User\controls\forms\ExpiredPasswordForm;
use app\User\controls\forms\PasswordForm;
use app\User\presenters\PasswordPresenter;
use app\User\presenters\SignPresenter;
use app\User\models\facades\AuthenticatorFacade;
use app\User\models\facades\PasswordFacade;
use app\User\models\facades\HistoryFacade;
use app\User\events\data\AuthenticationEvent;
use app\User\events\data\PasswordEvent;
use app\Base\factories\MailFactory;
use app\User\models\entities\User as UserEntity;
use app\Base\traits\TTranslator;
use app\Base\traits\TMail;
use Nette\Http\Session;
use Kdyby\Translation\ITranslator;
use Nette\Application\ApplicationException;

/**
 * PasswordEvents
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class PasswordEvents implements EventSubscriber
{
    use TTranslator;
    use TMail;

    const MESSAGE_SUCCESS_RESET="user.success.reset.title",
          MESSAGE_SUCCESS_RESET_COMMENT="user.success.reset.comment",
          MESSAGE_SUCCESS_PASSWORD="user.success.password.title",
          MESSAGE_SUCCESS_PASSWORD_COMMENT="user.success.password.comment",
          EMAIL_RESET_SUBJECT="user.email.passwordReset.subject",
          EMAIL_RESET_BODY="user.email.passwordReset.body",
          SUCCESS_LOGIN="user.success.login.title",
          SUCCESS_PASSWORD_CHANGE="user.success.password.title",
          ERROR_INVALID_PASSWORD="user.error.authentication.password";
    
    /**
     * @var Session
     */
    private $session;

    /**
     * @var PasswordFacade
     */
    private $passwordFacade;

    /**
     * @var HistoryFacade
     */
    private $historyFacade;

    /**
     * @param Session $session
     * @param MailFactory $mailFactory
     * @param PasswordFacade $passwordFacade
     * @param HistoryFacade $historyFacade
     * @param ITranslator $translator
     * @return void
     */
    public function __construct(
            Session $session,
            MailFactory $mailFactory,
            PasswordFacade $passwordFacade,
            HistoryFacade $historyFacade,
            ITranslator $translator=null)
    {
        $this->session = $session;
        $this->mailFactory = $mailFactory;
        $this->translator = $translator;
        $this->passwordFacade = $passwordFacade;
        $this->historyFacade = $historyFacade;
        return;
    }

    /**
     * get array of subscribed events
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ResetPasswordForm::EVENT_SUCCESS=>"onResetFormSuccess",
            ControlQuestionForm::EVENT_SUCCESS=>"onControlQuestionFormSuccess",
            ExpiredPasswordForm::EVENT_SUCCESS=>"onExpiredFormSuccess",
            PasswordForm::EVENT_SUCCESS=>"onPasswordFormSuccess",
            AuthenticatorFacade::EVENT_SUCCESS_RESET=>"onSuccessReset",
            AuthenticatorFacade::EVENT_SUCCESS_LOGIN=>"onSuccessLogin",
            AuthenticatorFacade::EVENT_INVALID_CREDENTIAL=>"onInvalidPassword",
            PasswordFacade::EVENT_CHANGE=>"onSuccessPasswordChange"
        ];
    }

    /**
     * process ResetForm
     * check username and email, save user data and redirect to control question
     * @param FormEvent $event
     * @return void
     */
    public function onResetFormSuccess(FormEvent $event){
        // load data from event
        $presenter = $event->getPresenter();
        $authenticator = $presenter->user->getAuthenticator();
        $control = $event->getControl();
        $values = $event->getValues();
        try {
            if($authenticator instanceof AuthenticatorFacade){
                // verify username and email
                $user = $authenticator->verifyReset(
                    $values->{ResetPasswordForm::USERNAME},
                    $values->{ResetPasswordForm::EMAIL}
                );
                // if successm save it to session with user id
                $section = $this->session->getSection(PasswordPresenter::RESET_PASSWORD_SESSION);
                if($user instanceof UserEntity){
                    $section->id = $user->getId();
                    $section->username = $user->getUsername();
                    $section->email = $user->getEmail();
                    $section->cQuestion = $user->getCQuestion();
                }
                // redirect to control quiestion form
                $presenter->redirect(PasswordPresenter::ACTION_CONTROL_QUESTION);
                return;
            } else {
                throw new ApplicationException();
            }
        } catch (AuthenticationException $exc) {
            // if not success do one of this actions
            switch ($exc->getCode()) {
                // if invalid username - show error in form
                case AuthenticationException::IDENTITY_NOT_FOUND:
                    $control->getItem(ResetPasswordForm::USERNAME)->addError($exc->getMessage());
                    $control->reload();
                    break;
                // if invalid email - show error in form
                case AuthenticationException::INVALID_EMAIL:
                    $control->getItem(ResetPasswordForm::EMAIL)->addError($exc->getMessage());
                    $control->reload();
                    break;
                // if anything else show flash message in presenter
                default:
                    $control->flashMessage(
                        $exc->getMessage(),
                        PasswordPresenter::STATUS_DANGER,
                        null,
                        PasswordPresenter::ICON_DANGER,
                        100);
                    $control->reload();
                    break;
            }
        }
    }

    /**
     * process control question form and reset password
     * @param FormEvent $event
     * @return void
     */
    public function onControlQuestionFormSuccess(FormEvent $event)
    {
        // load data from event
        $presenter = $event->getPresenter();
        $authenticator = $presenter->user->getAuthenticator();
        $control = $event->getControl();
        $values = $event->getValues();
        // load session section not found, redirect ro reset password form
        if(!$this->session->hasSection(PasswordPresenter::RESET_PASSWORD_SESSION)){
            $presenter->redirect(PasswordPresenter::ACTION_RESET);
            return;
        }
        // load data from session
        $section = $this->session->getSection(PasswordPresenter::RESET_PASSWORD_SESSION);
        try {
            if($authenticator instanceof AuthenticatorFacade){
                // verify answer a process password reset (facade fire event for new password send)
                $authenticator->verifyAnswerAndResetPassword ($section->id, $values[ControlQuestionForm::ANSWER]);
                // inform user about reset password and redirect to sign in form
                $presenter->flashMessage(
                    self::MESSAGE_SUCCESS_RESET,
                    PasswordPresenter::STATUS_SUCCESS,
                    self::MESSAGE_SUCCESS_RESET_COMMENT,
                    PasswordPresenter::ICON_SUCCESS,
                    50
                );
                $presenter->redirect(SignPresenter::ACTION_IN);
                return;
            } else {
                throw new ApplicationException;
            }
        } catch (AuthenticationException $exc) {
            // if verification failed do this actions
            // if answer was invalid show error on control question form
            if($exc->getCode()== AuthenticationException::INVALID_CONTROL_ANSWER){
                $control->getItem(ControlQuestionForm::ANSWER)->addError($exc->getMessage());
                $control->reload();
            }
            // if something else show flash message and redirect
            else{
                $control->flashMessage(
                    $exc->getMessage(),
                    PasswordPresenter::STATUS_DANGER,
                    null,
                    PasswordPresenter::ICON_DANGER,
                    100
                );
                $control->reload();
            }
        }

    }

    /**
     * process expired password form
     * @param FormEvent $event
     * @return void
     */
    public function onExpiredFormSuccess(FormEvent $event)
    {
        // load data from event
        $presenter = $event->getPresenter();
        $control = $event->getControl();
        $values = $event->getValues();
        // try to change expired password
        try {
            $this->passwordFacade->expired(
                $values->{ExpiredPasswordForm::USERNAME},
                $values->{ExpiredPasswordForm::OLD_PASSWORD},
                $values->{ExpiredPasswordForm::NEW_PASSWORD},
                $values->{ExpiredPasswordForm::REPEATED_PASSWORD}
            );
            // if success inform user and redirect to sign in form
            $presenter->flashMessage(
                self::MESSAGE_SUCCESS_PASSWORD,
                PasswordPresenter::STATUS_SUCCESS,
                self::MESSAGE_SUCCESS_PASSWORD_COMMENT,
                PasswordPresenter::ICON_SUCCESS,
                50
            );
            $presenter->redirect(SignPresenter::ACTION_IN);
            return;
        } catch (AuthenticationException $exc) {
            // if password change failed do ine of theese actions
            switch ($exc->getCode()) {
                // if username invalid - show error in form
                case AuthenticationException::IDENTITY_NOT_FOUND:
                    $control->getItem(ExpiredPasswordForm::USERNAME)->addError($exc->getMessage());
                    $control->reload();
                    break;
                // if password invalid - show error in form
                case AuthenticationException::INVALID_CREDENTIAL:
                    $control->getItem(ExpiredPasswordForm::OLD_PASSWORD)->addError($exc->getMessage());
                    $control->reload();
                    break;
                // if something else show flash message in presenter
                default:
                    $control->flashMessage($exc->getMessage(), PasswordPresenter::STATUS_DANGER);
                    $control->reload();
                    break;
            }
        } catch (ValidationException $exc) {
            // if new password and repeated password not same - show error in form
            if($exc->getCode()==ValidationException::NOT_SAME_PASSWORD){
                $control->getItem(ExpiredPasswordForm::REPEATED_PASSWORD)->addError($exc->getMessage());
                $control->reload();
            // if something else show flash message in presenter
            } else {
                $control->flashMessage($exc->getMessage(), PasswordPresenter::STATUS_DANGER);
                $control->reload();
            }
        }
    }

    public function onPasswordFormSuccess(FormEvent $event)
    {
        // load data from event
        $presenter = $event->getPresenter();
        $control = $event->getControl();
        $values = $event->getValues(true);
        // try to change password
        try {
            $this->passwordFacade->setUser($presenter->getUser());
            $this->passwordFacade->change(
                (int) $values[PasswordForm::ID],
                $values[PasswordForm::OLD_PASSWORD],
                $values[PasswordForm::NEW_PASSWORD],
                $values[PasswordForm::REPEATED_PASSWORD]
            );
            // and then show flash message in control
            $control->flashMessage(
                self::MESSAGE_SUCCESS_PASSWORD,
                PasswordPresenter::STATUS_SUCCESS,
                self::MESSAGE_SUCCESS_PASSWORD_COMMENT,
                PasswordPresenter::ICON_SUCCESS,
                100
            );
            $control->reload();
        } catch (AuthenticationException $exc) {
            // if old password was invalid show error in form
            if($exc->getCode()==AuthenticationException::INVALID_CREDENTIAL){
                $control->getItem(PasswordForm::OLD_PASSWORD)
                        ->addError($exc->getMessage());
                $control->reload();
            } else {
                throw $exc;
            }
        } catch (ValidationException $exc) {
            // if new password and repeated password are not same show error in form
            if($exc->getCode()== ValidationException::NOT_SAME_PASSWORD){
                $control->getItem(PasswordForm::REPEATED_PASSWORD)
                        ->addError($exc->getMessage());
                $control->reload();
            } else {
                throw $exc;
            }
        } catch (ProfileException $exc) {
            // if user not found show error on main page
            if($exc->getCode()== ProfileException::NOT_FOUND){
                $presenter->flashMessage(
                    $exc->getMessage(),
                    PasswordPresenter::STATUS_DANGER,
                    null,
                    PasswordPresenter::ICON_DANGER,
                    50
                );
                $presenter->redirect(PasswordPresenter::ACL_ERROR_LINK);
            } else {
                throw $exc;
            }
        } catch (PermissionException $exc){
            // if operation not permitted show error on main page
            if($exc->getCode()== PermissionException::OPERATION_NOT_PERMITTED){
                $presenter->flashMessage(
                    $exc->getMessage(),
                    PasswordPresenter::STATUS_DANGER,
                    null,
                    PasswordPresenter::ICON_DANGER,
                    50
                );
                $presenter->redirect(PasswordPresenter::ACL_ERROR_LINK);
            } else {
                throw $exc;
            }
        }
    }

    /**
     * send new password to user
     * @param AuthenticationEvent $event
     * @return void
     */
    public function onSuccessReset(AuthenticationEvent $event)
    {
        // save reset password log to history
        $this->historyFacade->save(
            $event->{AuthenticationEvent::ENTITY},
            self::MESSAGE_SUCCESS_RESET
        );
        // inform user about password reset and send him new one
        $this->mailUser(
            $event->{AuthenticationEvent::VALUES}->{UserEntity::EMAIL},
            $event->{AuthenticationEvent::VALUES}->{UserEntity::REALNAME},
            $this->_(self::EMAIL_RESET_SUBJECT),
            $this->_(self::EMAIL_RESET_BODY,
                    [
                        AuthenticationEvent::USERNAME=>$event->{AuthenticationEvent::VALUES}->{UserEntity::USERNAME},
                        AuthenticationEvent::EMAIL=>$event->{AuthenticationEvent::VALUES}->{UserEntity::PASSWORD}
                    ])
        );
        return;
    }

    /**
     * log success login
     * @param AuthenticationEvent $event
     * @return void
     */
    public function onSuccessLogin(AuthenticationEvent $event)
    {
        $this->historyFacade->save(
            $event->{AuthenticationEvent::ENTITY},
            self::SUCCESS_LOGIN
        );
        return;
    }

    /**
     * log invalid password
     * @param AuthenticationEvent $event
     * @return void
     */
    public function onInvalidPassword(AuthenticationEvent $event)
    {
        $this->historyFacade->save(
            $event->{AuthenticationEvent::ENTITY},
            self::ERROR_INVALID_PASSWORD
        );
        return;
    }

    /**
     * log change password
     * @param PasswordEvent $event
     * @return void
     */
    public function onSuccessPasswordChange(PasswordEvent $event)
    {
        $this->historyFacade->save(
            $event->{PasswordEvent::ENTITY},
            self::SUCCESS_PASSWORD_CHANGE
        );
        return;
    }
}