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

use app\Base\controls\FormControl\events\FormEvent;
use app\User\models\exceptions\AuthenticationException;
use app\User\presenters\PasswordPresenter;
use app\User\presenters\MainPresenter;
use app\User\controls\forms\SignInForm;
use Contributte\EventDispatcher\EventSubscriber;
use app\Base\presenters\AbstractPresenter;
use Nette\Application\InvalidPresenterException;

/**
 * SignInEvents
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class SignInEvents implements EventSubscriber
{
    /**
     * get array of subscribed events
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            SignInForm::EVENT_SUCCESS=>"onFormSuccess"
        ];
    }

    /**
     * process sign in form
     * check username, password, user status, maximum attempts and password expiration
     * login user, show arror or redirect to reset password or expired password form
     * @param FormEvent $event
     * @return void
     */
    public function onFormSuccess(FormEvent $event)
    {
        $presenter = $event->getPresenter();
        $control = $event->getControl();
        if(!$presenter instanceof AbstractPresenter){
            throw new InvalidPresenterException();
        }
        try {
            $values = $event->getValues();
            $presenter->user->login($values->{SignInForm::USERNAME}, $values->{SignInForm::PASSWORD});
            $presenter->redirect(MainPresenter::ACTION_DEFAULT);
        } catch (AuthenticationException $exc) {
            switch ($exc->getCode()) {
                case AuthenticationException::IDENTITY_NOT_FOUND:
                    $control->getItem(SignInForm::USERNAME)
                            ->addError($exc->getMessage());
                    $control->reload();
                    break;

                case AuthenticationException::MAX_ATTEMPTS_REACHED:
                    $presenter->flashMessage(
                        $exc->getMessage(),
                        PasswordPresenter::STATUS_WARNING,
                        null,
                        PasswordPresenter::ICON_WARNING,
                        50
                    );
                    $presenter->redirect(PasswordPresenter::ACTION_RESET);
                    break;

                case AuthenticationException::PASSWORD_EXPIRED:
                    $presenter->flashMessage(
                        $exc->getMessage(),
                        PasswordPresenter::STATUS_INFO,
                        null,
                        PasswordPresenter::ICON_INFO,
                        50
                    );
                    $presenter->redirect(PasswordPresenter::ACTION_EXPIRED);
                    break;

                case AuthenticationException::INVALID_CREDENTIAL:
                    $control->getItem(SignInForm::PASSWORD)
                            ->addError($exc->getMessage());
                    $control->reload();
                    break;

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
}