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
use app\User\models\facades\SettingsFacade;
use app\User\controls\grids\UserSettingsGrid;
use app\Base\controls\GridControl\events\GridEventData;
use app\User\presenters\ProfilePresenter;
use app\User\models\exceptions\ProfileException;
use app\User\models\exceptions\SettingsException;
use app\User\events\data\SettingsEvent;
use Nette\Security\User;

/**
 * SettingsEvents
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class SettingsEvents implements EventSubscriber
{
    const MESSAGE_SUCCESS_RELOAD="user.success.settings.reload",
          MESSAGE_SUCCESS_RESET="user.success.settings.reset",
          MESSAGE_SUCCESS_SAVE="user.success.settings.title";

    /**
     * @var SettingsFacade
     */
    private $settingsFacade;

    /**
     * @var User
     */
    private $user;

    /**
     * @var UserSettingsGrid | null
     */
    private $control;

    /**
     * get subscribed events
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            UserSettingsGrid::EVENT_CLICK_RELOAD=>"onClickReload",
            UserSettingsGrid::EVENT_CLICK_RESET=>"onClickReset",
            UserSettingsGrid::EVENT_EDIT_VALUE=>"onEditableValueSubmit"
        ];
    }

    /**
     * @param SettingsFacade $settingsFacade
     * @param User $user
     * @return void
     */
    public function __construct(SettingsFacade $settingsFacade, User $user)
    {
        $this->settingsFacade = $settingsFacade;
        $this->user = $user;
        return;
    }

    /**
     * process click on reload button event
     * @param GridEventData $event
     * @return void
     */
    public function onClickReload(GridEventData $event)
    {
        $this->control = $event->getControl();
        // try to reload user settings
        try {
            $this->settingsFacade->reload($this->user->getId());
            $this->control->flashMessage(
                self::MESSAGE_SUCCESS_RELOAD,
                ProfilePresenter::STATUS_SUCCESS,
                null,
                ProfilePresenter::ICON_SUCCESS,
                100
            );
            $this->control->reload();
        } catch (ProfileException $exc) {
            // if failed show message
            $this->control->flashMessage(
                $exc->getMessage(),
                ProfilePresenter::STATUS_DANGER,
                null,
                ProfilePresenter::ICON_DANGER,
                100
            );
            $this->control->reload();
        }
    }

    /**
     * process click on reset button event
     * @param GridEventData $event
     * @return void
     */
    public function onClickReset(GridEventData $event)
    {
        $this->control = $event->getControl();
        // try to reset user settings to default
        try {
            $this->settingsFacade->reset($this->user->getId());
            $this->control->flashMessage(
                self::MESSAGE_SUCCESS_RESET,
                ProfilePresenter::STATUS_SUCCESS,
                null,
                ProfilePresenter::ICON_SUCCESS,
                100
            );
            $this->control->reload();
        } catch (ProfileException $exc) {
            // if failed show message
            $this->control->flashMessage(
                $exc->getMessage(),
                ProfilePresenter::STATUS_DANGER,
                null,
                ProfilePresenter::ICON_DANGER,
                100
            );
            $this->control->reload();
        }
    }

    /**
     * process submit editable value
     * @param GridEventData $event
     * @return void
     */
    public function onEditableValueSubmit(GridEventData $event)
    {
        $data = $event->getData();
        $this->control = $event->getControl();
        // try to save value
        try {
            if ($data[UserSettingsGrid::VALUE]==$this->control->_(UserSettingsGrid::YES_NO[1])){
                $value=1;
            } else {
                $value=0;
            }
            $this->settingsFacade->save($data[UserSettingsGrid::ID], $value);
            $this->control->flashMessage(
                self::MESSAGE_SUCCESS_SAVE,
                ProfilePresenter::STATUS_SUCCESS,
                null,
                ProfilePresenter::ICON_SUCCESS,
                100
            );
        $this->control->reload();
        } catch (SettingsException $exc) {
            $this->control->flashMessage(
                $exc->getMessage(),
                ProfilePresenter::STATUS_DANGER,
                null,
                ProfilePresenter::ICON_DANGER,
                100
            );
            $this->control->reload();
        }
    }
}
