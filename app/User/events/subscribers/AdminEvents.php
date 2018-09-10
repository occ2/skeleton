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
use app\User\controls\grids\UsersAdminGrid;
use app\User\controls\forms\UsersAdminForm;
use app\Base\controls\GridControl\events\GridRowEventData;
use app\Base\controls\FormControl\events\FormEvent;
use app\User\events\data\AdminEvent;
use app\User\models\facades\AdminFacade;
use app\User\models\facades\SettingsFacade;
use app\User\models\facades\RolesFacade;
use app\User\models\exceptions\AdminException;
use app\Base\factories\MailFactory;
use app\User\presenters\AdminPresenter;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * AdminEvents
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1..0
 */
final class AdminEvents implements EventSubscriber
{
    const MESSAGE_SUCCESS_ADD="user.success.user.add";
    const MESSAGE_SUCCESS_EDIT="user.success.user.edit";

    /**
     * @var AdminFacade
     */
    private $adminFacade;

    /**
     * @var SettingsFacade
     */
    private $settingsFacade;

    /**
     * @var RolesFacade
     */
    private $rolesFacade;

    /**
     * @var MailFactory
     */
    private $mailFactory;

    /**
     * @param AdminFacade $adminFacade
     * @param SettingsFacade $settingsFacade
     * @param RolesFacade $rolesFacade
     * @param MailFactory $mailFactory
     * @return void
     */
    public function __construct(AdminFacade $adminFacade, SettingsFacade $settingsFacade, RolesFacade $rolesFacade, MailFactory $mailFactory)
    {
        $this->adminFacade = $adminFacade;
        $this->settingsFacade = $settingsFacade;
        $this->rolesFacade = $rolesFacade;
        $this->mailFactory = $mailFactory;
        return;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            UsersAdminForm::ON_SUCCESS=>"onFormSuccess",
            UsersAdminGrid::EVENT_DELETE=>"onConfirmDelete",
            UsersAdminGrid::EVENT_RESET=>"onConfirmReset",
            AdminFacade::EVENT_ADD=>"onAddUser"
        ];
    }

    /**
     * process user form (add or edit)
     * @param FormEvent $event
     * @return void
     */
    public function onFormSuccess(FormEvent $event)
    {
        $values = $event->getValues();
        $control = $event->getControl();
        $presenter = $event->getPresenter();
        $form = $event->getForm();
        if($values->id == null){
            $this->addUser($values, $control, $presenter, $form);
        } else {
            $this->saveUser($values, $control, $presenter, $form);
        }
        return;
    }

    /**
     * try to add user
     * @param ArrayHash $values
     * @param UsersAdminForm $control
     * @param AdminPresenter $presenter
     * @param Form $form
     * @return void
     * @throws AdminException
     */
    private function addUser(ArrayHash $values, UsersAdminForm $control, AdminPresenter $presenter,Form $form){
        try {
            $this->adminFacade->add((array) $values);
            $presenter->flashMessage(
                self::MESSAGE_SUCCESS_ADD,
                AdminPresenter::STATUS_SUCCESS,
                null,
                AdminPresenter::ICON_SUCCESS,
                50
            );
            $presenter->redirect(AdminPresenter::ACTION_DEFAULT);
            return;
        } catch (AdminException $exc) {
            if($exc->getCode() == AdminException::USERNAME_NOT_UNIQUE){
                $control->getColumn(UsersAdminForm::USERNAME)->addError(AdminException::MESSAGE_NOT_UNIQUE);
                $control->reload();
            } else {
                throw $exc;
            }
        }
    }

    /**
     * try to save edited data
     * @param ArrayHash $values
     * @param UsersAdminForm $control
     * @param AdminPresenter $presenter
     * @param Form $form
     * @throws AdminException
     * @return void
     */
    private function saveUser(ArrayHash $values, UsersAdminForm $control, AdminPresenter $presenter,Form $form){
        try {
            $this->adminFacade->save((array) $values);
            $presenter->flashMessage(
                self::MESSAGE_SUCCESS_EDIT,
                AdminPresenter::STATUS_SUCCESS,
                null,
                AdminPresenter::ICON_SUCCESS,
                75
            );
            $presenter->redirect(AdminPresenter::ACTION_DEFAULT);
        } catch (AdminException $exc) {
            if($exc->getCode() == AdminException::USERNAME_NOT_UNIQUE){
                $control->getColumn(UsersAdminForm::USERNAME)->addError(AdminException::MESSAGE_NOT_UNIQUE);
                $control->reload();
            } elseif ($exc->getCode() == AdminException::NOT_FOUND) {
                $control->flashMessage(
                    AdminException::MESSAGE_NOT_FOUND,
                    AdminPresenter::STATUS_DANGER,
                    null,
                    AdminPresenter::ICON_DANGER,
                    100
                );
            } else {
                throw $exc;
            }
        }
    }

    // todo
    public function onConfirmDelete(GridRowEventData $event)
    {
        bdump($event);
    }

    // todo
    public function onConfirmReset(GridRowEventData $event)
    {
        bdump($event);
    }

    // todo - set default settings, set default roles
    public function onAddUser(AdminEvent $event)
    {

    }
}
