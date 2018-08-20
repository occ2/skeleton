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

use app\Base\factories\MailFactory;
use app\Base\controls\FormControl\events\FormEvent;
use app\User\events\data\ProfileEvent;
use app\User\models\facades\ProfileFacade;
use app\User\models\facades\HistoryFacade;
use app\User\controls\forms\ProfileForm;
use app\User\presenters\ProfilePresenter;
use app\User\models\exceptions\ProfileException;
use app\Base\traits\TTranslator;
use app\Base\traits\TMail;
use app\User\models\facades\TTestUser;
use Contributte\EventDispatcher\EventSubscriber;
use Kdyby\Translation\ITranslator;
use Nette\Security\User;

/**
 * ProfileEvents
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class ProfileEvents implements EventSubscriber
{
    use TTranslator;
    use TMail;
    use TTestUser;

    const SUCCESS_SAVE_TITLE="user.success.profile.title",
          SUCCESS_SAVE_COMMENT="user.success.profile.comment";

    /**
     * @var ProfileFacade
     */
    private $profileFacade;

    /**
     * @var HistoryFacade
     */
    private $historyFacade;

    /**
     * get subscribed events
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ProfileForm::EVENT_SUCCESS=>"onProfileFormSuccess",
            ProfileFacade::EVENT_SAVE=>"onProfileSave"
        ];
    }

    /**
     * @param User $user
     * @param ITranslator $translator
     * @param MailFactory $mailFactory
     * @param HistoryFacade $historyFacade
     * @param ProfileFacade $profileFacade
     * @return void
     */
    public function __construct(
        User $user,
        ITranslator $translator,
        MailFactory $mailFactory,
        HistoryFacade $historyFacade,
        ProfileFacade $profileFacade
    )
    {
        $this->mailFactory = $mailFactory;
        $this->translator = $translator;
        $this->profileFacade = $profileFacade;
        $this->historyFacade = $historyFacade;
        $this->user = $user;
        return;
    }

    /**
     * process profile form
     * @param FormEvent $event
     * @return void
     */
    public function onProfileFormSuccess(FormEvent $event)
    {
        // load data from event
        $presenter = $event->getPresenter();
        $control = $event->getControl();
        $values = $event->getValues(true);
        // set current user to profile facade
        $this->profileFacade->setUser($presenter->getUser());
        $identity = $presenter->getUser()->getIdentity();
        // try to save data
        try {
            // if control answer not changed delete it from form values
            if(empty($values[ProfileForm::ANSWER])){
                unset($values[ProfileForm::ANSWER]);
            }
            // save data to profile
            $this->profileFacade->save((array) $values);
            // update current user identity (in session)
            foreach ($values as $key=>$value){
                if($identity->{$key}!=$value){
                    $identity->{$key}=$value;
                }
            }
            // show flash message on form
            $control->flashMessage(
                self::SUCCESS_SAVE_TITLE,
                ProfilePresenter::STATUS_SUCCESS,
                self::SUCCESS_SAVE_COMMENT,
                ProfilePresenter::ICON_SUCCESS,
                100
            );
            $control->reload();
            $presenter->redirect(ProfilePresenter::ACTION_DEFAULT);
            return;
        } catch (ProfileException $exc) {
            // if saving fail show flash message in presenter
            $presenter->flashMessage(
                $exc->getMessage(),
                ProfilePresenter::STATUS_DANGER,
                null,
                ProfilePresenter::ICON_DANGER,
                50
            );
            $presenter->redirect(ProfilePresenter::ACTION_DEFAULT);
        }
    }

    /**
     * log profile change into the history
     * @param ProfileEvent $event
     * @return void
     */
    public function onProfileSave(ProfileEvent $event)
    {
        $this->historyFacade->save(
            $event->{ProfileEvent::ENTITY},
            self::SUCCESS_SAVE_TITLE
        );
        return;        
    }
}
