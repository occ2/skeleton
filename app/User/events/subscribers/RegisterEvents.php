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
use app\User\events\data\ProfileEvent;
use app\User\controls\forms\RegisterForm;
use app\User\models\exceptions\ProfileException;
use app\User\models\facades\ProfileFacade;
use app\User\models\facades\RolesFacade;
use app\User\models\facades\SettingsFacade;
use app\User\models\facades\HistoryFacade;
use app\User\models\facades\AdminFacade;
use app\User\presenters\SignPresenter;
use app\Base\factories\MailFactory;
use Contributte\EventDispatcher\EventSubscriber;
use app\Base\traits\TMail;
use app\Base\traits\TTranslator;
use app\Base\presenters\AbstractPresenter as Presenter;
use Nette\Forms\Controls\BaseControl;
use Kdyby\Translation\ITranslator;

/**
 * RegisterEvents
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class RegisterEvents implements EventSubscriber
{
    use TMail;
    use TTranslator;

    const MESSAGE_SUCCESS_TITLE="user.success.register.title",
          MESSAGE_SUCCESS_COMMENT="user.success.register.comment",

          REGISTER_MAILUSER_SUBJECT="user.email.register.subject",
          REGISTER_MAILUSER_BODY="user.email.register.body",
          REGISTER_MAILADMIN_SUBJECT="user.email.registerAdmin.subject",
          REGISTER_MAILADMIN_BODY="user.email.registerAdmin.body";

    /**
     * @var ProfileFacade
     */
    private $profileFacade;

    /**
     * @var SettingsFacade
     */
    private $settingsFacade;

    /**
     * @var RolesFacade
     */
    private $rolesFacade;

    /**
     * @var HistoryFacade
     */
    private $historyFacade;

    /**
     * @var AdminFacade
     */
    private $adminFacade;

    /**
     * @param ProfileFacade $profileFacade
     * @param MailFactory $mailFactory
     * @param SettingsFacade $settingsFacade
     * @param RolesFacade $rolesFacade
     * @param HistoryFacade $historyFacade
     * @param AdminFacade $adminFacade
     * @param ITranslator $translator
     * @return void
     */
    public function __construct(
        ProfileFacade $profileFacade,
        MailFactory $mailFactory,
        SettingsFacade $settingsFacade,
        RolesFacade $rolesFacade,
        HistoryFacade $historyFacade,
        AdminFacade $adminFacade,
        ITranslator $translator=null
    )
    {
        $this->profileFacade = $profileFacade;
        $this->settingsFacade = $settingsFacade;
        $this->rolesFacade = $rolesFacade;
        $this->historyFacade = $historyFacade;
        $this->adminFacade = $adminFacade;
        $this->mailFactory = $mailFactory;
        $this->translator = $translator;
        return;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            RegisterForm::EVENT_SUCCESS=>"onFormSuccess",
            ProfileFacade::EVENT_REGISTER=>"onRegister"
        ];
    }

    /**
     * process register form
     * @param FormEvent $event
     * @return void
     */
    public function onFormSuccess(FormEvent $event)
    {
        $control = $event->getControl();
        $presenter = $event->getPresenter();
        $values = (array) $event->getValues(true);
        try {
            $this->profileFacade->register($values, [RegisterForm::REPEATED_PASSWORD]);
            if($presenter instanceof Presenter){
                $presenter->flashMessage(
                    static::MESSAGE_SUCCESS_TITLE,
                    SignPresenter::STATUS_SUCCESS,
                    static::MESSAGE_SUCCESS_COMMENT,
                    SignPresenter::ICON_SUCCESS,
                    50
                );
                $presenter->redirect(SignPresenter::ACTION_IN);
            }
        } catch (ProfileException $exc) {
            if($exc->getCode()==ProfileException::USERNAME_NOT_UNIQUE){
                $item = $control->getItem(RegisterForm::USERNAME);
                if($item instanceof BaseControl){
                    $item->addError($exc->getMessage());
                }                        
            } else {
                $control->flashMessage(
                    $exc->getMessage(),
                    SignPresenter::STATUS_DANGER,
                    null,
                    SignPresenter::ICON_DANGER,
                    50
                );
                $control->reload();
            }
        }
        return;
    }

    public function onRegister(ProfileEvent $event)
    {
        $user = $event->entity;
        // set default settings
        $this->settingsFacade->reset($user->getId());

        // set default roles
        $this->rolesFacade->setDefault($user);

        // save record to user history
        $this->historyFacade->save($user, self::MESSAGE_SUCCESS_TITLE);

        // inform user about registration
        $this->mailUser(
            $user->getEmail(),
            $user->getRealname(),
            $this->_(self::REGISTER_MAILUSER_SUBJECT),
            $this->_(self::REGISTER_MAILUSER_BODY,[
                ProfileEvent::USERNAME=>$user->getUsername(),
                ProfileEvent::REALNAME=>$user->getRealname(),
                ProfileEvent::EMAIL=>$user->getEmail()
            ])
        );

        // inform admins about registration
        $this->mailAdmin(
            $this->adminFacade,
            $this->_(self::REGISTER_MAILADMIN_SUBJECT),
            $this->_(self::REGISTER_MAILADMIN_BODY,[
                ProfileEvent::USERNAME=>$user->getUsername(),
                ProfileEvent::REALNAME=>$user->getRealname(),
                ProfileEvent::EMAIL=>$user->getEmail()
            ])
        );
        return;
    }
}