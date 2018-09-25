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
use app\User\models\facades\HistoryFacade;
use app\User\models\exceptions\AdminException;
use app\Base\factories\MailFactory;
use app\User\presenters\AdminPresenter;
use app\Base\traits\TMail;
use app\Base\traits\TTranslator;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Kdyby\Translation\ITranslator;

/**
 * AdminEvents
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class AdminEvents implements EventSubscriber
{
    use TMail;
    use TTranslator;

    const MESSAGE_SUCCESS_ADD="user.success.user.add";
    const MESSAGE_SUCCESS_EDIT="user.success.user.edit";
    const MESSAGE_SUCCESS_RESET="user.success.user.reset";
    const MESSAGE_SUCCESS_DELETE="user.success.user.delete";
    const EMAIL_ADD_SUBJECT="user.email.add.subject";
    const EMAIL_ADD_BODY="user.email.add.body";
    const EMAIL_RESET_SUBJECT="user.email.reset.subject";
    const EMAIL_RESET_BODY="user.email.reset.body";

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
     * @var HistoryFacade
     */
    private $historyFacade;

    /**
     * @param AdminFacade $adminFacade
     * @param SettingsFacade $settingsFacade
     * @param RolesFacade $rolesFacade
     * @param MailFactory $mailFactory
     * @return void
     */
    public function __construct(AdminFacade $adminFacade, SettingsFacade $settingsFacade, RolesFacade $rolesFacade, HistoryFacade $historyFacade, MailFactory $mailFactory, ITranslator $translator)
    {
        $this->adminFacade = $adminFacade;
        $this->settingsFacade = $settingsFacade;
        $this->rolesFacade = $rolesFacade;
        $this->historyFacade = $historyFacade;
        $this->mailFactory = $mailFactory;
        $this->translator = $translator;
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
            AdminFacade::EVENT_ADD=>"onAddUser",
            AdminFacade::EVENT_RESET_PASSWORD=>"onResetPassword"
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

    /**
     * try to delete user
     * @param GridRowEventData $event
     * @return void
     */
    public function onConfirmDelete(GridRowEventData $event)
    {
        $presenter = $event->getControl()->getPresenter();
        $id = $event->getId();
        try {
            $this->adminFacade->remove($id);
            $presenter->flashMessage(
                self::MESSAGE_SUCCESS_DELETE,
                AdminPresenter::STATUS_SUCCESS,
                null,
                AdminPresenter::ICON_SUCCESS,
                75
            );
            $presenter->redirect(AdminPresenter::ACTION_DEFAULT);
            return;
        } catch (AdminException $exc) {
            $presenter->flashMessage(
                $exc->getMessage(),
                AdminPresenter::STATUS_DANGER,
                null,
                AdminPresenter::ICON_DANGER,
                100
            );
            $presenter->redirect(AdminPresenter::ACTION_DEFAULT);
        }
    }

    /**
     * try to reset users password
     * @param GridRowEventData $event
     * @return void
     */
    public function onConfirmReset(GridRowEventData $event)
    {
        $presenter = $event->getControl()->getPresenter();
        $id = $event->getId();
        try {
            $this->adminFacade->resetPassword($id);
            $presenter->flashMessage(
                self::MESSAGE_SUCCESS_RESET,
                AdminPresenter::STATUS_INFO,
                null,
                AdminPresenter::ICON_INFO,
                100);
            $presenter->redirect(AdminPresenter::ACTION_DEFAULT);
            return;
        } catch (AdminException $exc) {
            $presenter->flashMessage(
                $exc->getMessage(),
                AdminPresenter::STATUS_DANGER,
                null,
                AdminPresenter::ICON_DANGER,
                100
            );
            $presenter->redirect(AdminPresenter::ACTION_DEFAULT);
        }
    }

    // todo
    public function onChangeStatus(GridRowEventData $event)
    {

    }

    /**
     * add user default roles, settings, history and email user new password
     * @param AdminEvent $event
     * @return void
     */
    public function onAddUser(AdminEvent $event)
    {
        $user = $event->{AdminEvent::ENTITY};
        $password = $event->{AdminEvent::PASSWORD};
        $this->rolesFacade->setDefault($user);
        $this->settingsFacade->reset($user->getId());
        $this->historyFacade->save($user, "user.success.user.add");
        $this->mailUser(
            $user->getEmail(),
            $user->getRealname(),
            $this->_(self::EMAIL_ADD_SUBJECT),
            $this->_(self::EMAIL_ADD_BODY,
                [
                    "realname"=>$user->getRealname(),
                    "username"=>$user->getUsername(),
                    "password"=>$password,
                    "email"=>$user->getEmail()
                ]
            )
        );
        return;
    }

    public function onResetPassword(AdminEvent $event)
    {
        $user = $event->{AdminEvent::ENTITY};
        $password = $event->{AdminEvent::PASSWORD};
        $this->mailUser(
            $user->getEmail(),
            $user->getRealname(),
            $this->_(self::EMAIL_RESET_SUBJECT),
            $this->_(self::EMAIL_RESET_BODY,[
                "username"=>$user->getUsername(),
                "password"=>$password
            ])
        );
        return;
    }
}
