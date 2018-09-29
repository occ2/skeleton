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

namespace app\User\presenters;

use app\User\controls\forms\UsersAdminForm;
use app\User\controls\grids\UsersAdminGrid;
use app\User\controls\grids\UserRolesGrid;
use app\User\controls\grids\UserHistoryGrid;
use app\User\controls\grids\UserSettingsGrid;
use app\User\models\exceptions\ProfileException;
use app\User\models\entities\User as UserEntity;

/**
 * AdminPresenter
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class AdminPresenter extends BasePresenter
{
    const ACTION_DEFAULT=":User:Admin:default",
          ACTION_HISTORY=":User:Admin:history",
          ACTION_SETTINGS=":User:Admin:settings",
          ACTION_ROLES=":User:Admin:roles",
          ACTION_ADD=":User:Admin:add",
          ACTION_EDIT=":User:Admin:edit",
          ACTION_RESET=":User:Admin:reset",
          USERS_GRID="usersAdminGrid",
          USERS_FORM="usersAdminForm",
          ROLES_GRID="userRolesGrid",
          HISTORY_GRID="userHistoryGrid",
          SETTINGS_GRID="userSettingsGrid";

    /**
     * @inject
     * @var \app\User\models\facades\AdminFacade
     */
    public $adminFacade;

    /**
     * @inject
     * @var \app\User\models\facades\HistoryFacade
     */
    public $historyFacade;

    /**
     * @inject
     * @var \app\User\models\facades\RolesFacade
     */
    public $rolesFacade;

    /**
     * @inject
     * @var \app\User\models\facades\SettingsFacade
     */
    public $settingsFacade;

    /**
     * @inject
     * @var \app\User\controls\factories\IUsersAdminGrid
     */
    public $usersAdminGridFactory;

    /**
     * @inject
     * @var \app\User\controls\factories\IUsersAdminForm
     */
    public $usersAdminFormFactory;


    /**
     * @inject
     * @var \app\User\controls\factories\IUserRolesGrid
     */
    public $userRolesGridFactory;

    /**
     * @inject
     * @var \app\User\controls\factories\IUserSettingsGrid
     */
    public $userSettingsGridFactory;

    /**
     * @inject
     * @var \app\User\controls\factories\IUserHistoryGrid
     */
    public $userHistoryGridFactory;

    /**
     * @var int
     */
    public $id=null;

    /**
     * @title user.navbar.administration
     * @acl (resource="users",privilege="read")
     * @breadcrumb (key="home",active="true")
     * @breadcrumb (key="user.admin.default", name="user.navbar.administration", link=":User:Admin:default", active=false)
     */
    public function actionDefault()
    {
        $this[self::USERS_GRID]->setDatasource($this->adminFacade->load());;
        return;
    }

    /**
     * @title user.usersAdminGrid.action.history
     * @param int $id
     * @acl (resource="users",privilege="read")
     * @breadcrumb (key="home",active="true")
     * @breadcrumb (key="user.admin.default", name="user.navbar.administration", link=":User:Admin:default", active=true)
     * @breadcrumb (key="user.admin.history", name="user.usersAdminGrid.action.history", link=":User:Admin:history")
     * @return void
     */
    public function actionHistory($id)
    {
        $this->id = $id;
        try {
            $user = $this->adminFacade->get($id);
            if($user==null){
                throw new ProfileException(ProfileException::MESSAGE_NOT_FOUND,ProfileException::NOT_FOUND);
            }
            $history = $this->historyFacade->load($user);
            $appendTitle = " - " . $user->getRealname();
            $this->appendToTitle($appendTitle);
            $this[self::HISTORY_GRID]->setDatasource($history);
            $this[self::HISTORY_GRID]->appendToTitle($appendTitle);
            $this[self::BREADCRUMBS]->appendToItem("user.admin.history",$appendTitle);
            return;
        } catch (ProfileException $exc) {
            $this->flashMessage(
                $exc->getMessage(),
                self::STATUS_DANGER,
                null,
                self::ICON_DANGER,
                100);
        }
    }

    /**
     * @title user.usersAdminGrid.action.settings
     * @param int $id
     * @acl (resource="users",privilege="read")
     * @breadcrumb (key="home",active="true")
     * @breadcrumb (key="user.admin.default", name="user.navbar.administration", link=":User:Admin:default", active=true)
     * @breadcrumb (key="user.admin.settings", name="user.usersAdminGrid.action.settings", link=":User:Admin:settings")
     * @return void
     */
    public function actionSettings($id)
    {
        $this->id = $id;
        try {
            $user = $this->adminFacade->get($id);
            if($user==null){
                throw new ProfileException(ProfileException::MESSAGE_NOT_FOUND,ProfileException::NOT_FOUND);
            }
            $appendTitle = " - " . $user->getRealname();
            $this->appendToTitle($appendTitle);
            $settings = $this->settingsFacade->load($user);
            $this[self::SETTINGS_GRID]->setDatasource($settings);
            $this[self::SETTINGS_GRID]->appendToTitle($appendTitle);
            $this[self::BREADCRUMBS]->appendToItem("user.admin.settings",$appendTitle);
        } catch (ProfileException $exc) {
            $this->flashMessage(
                $exc->getMessage(),
                self::STATUS_DANGER,
                null,
                self::ICON_DANGER,
                100);
        }
    }

    /**
     * @title user.usersAdminGrid.action.roles
     * @param int $id
     * @acl (resource="users",privilege="read")
     * @breadcrumb (key="home",active="true")
     * @breadcrumb (key="user.admin.default", name="user.navbar.administration", link=":User:Admin:default", active=true)
     * @breadcrumb (key="user.admin.roles", name="user.usersAdminGrid.action.roles", link=":User:Admin:roles")
     * @return void
     */
    public function actionRoles($id)
    {
        $this->id = $id;
        try {
            $user = $this->adminFacade->get($id);
            if($user==null){
                throw new ProfileException(ProfileException::MESSAGE_NOT_FOUND,ProfileException::NOT_FOUND);
            }
            $roles = $this->rolesFacade->load($user);
            $appendTitle = " - " . $user->getRealname();
            $this->appendToTitle($appendTitle);
            $this[self::ROLES_GRID]->setDatasource($roles);
            $this[self::ROLES_GRID]->appendToTitle($appendTitle);
            $this[self::BREADCRUMBS]->appendToItem("user.admin.roles",$appendTitle);
            return;
        } catch (ProfileException $exc) {
            $this->flashMessage(
                $exc->getMessage(),
                self::STATUS_DANGER,
                null,
                self::ICON_DANGER,
                100);
        }
    }

    /**
     * @title user.usersAdminForm.title
     * @acl (resource="users",privilege="write")
     * @breadcrumb (key="home",active="true")
     * @breadcrumb (key="user.admin.default", name="user.navbar.administration", link=":User:Admin:default", active=true)
     * @breadcrumb (key="user.admin.add", name="user.usersAdminForm.title", link=":User:Admin:add")
     * @return void
     */
    public function actionAdd()
    {
        $this->id=null;
        return;
    }

    /**
     * @title user.usersAdminGrid.action.edit
     * @param int $id
     * @acl (resource="users",privilege="write")
     * @breadcrumb (key="home",active="true")
     * @breadcrumb (key="user.admin.default", name="user.navbar.administration", link=":User:Admin:default", active=true)
     * @breadcrumb (key="user.admin.edit", name="user.usersAdminGrid.action.edit", link=":User:Admin:edit")
     * @return void
     */
    public function actionEdit($id)
    {
        $this->id = $id;
        $user = $this->adminFacade->get($this->id);
        if($user instanceof UserEntity) {
            
            $this->appendToTitle($user->getRealname());
            $this[self::USERS_FORM]->setDefaults($user->toArrayHash());
            $this[self::USERS_FORM]->setTitle("user.usersAdminGrid.action.edit");
            $this[self::USERS_FORM]->appendToTitle($user->getRealname());
            $this[self::BREADCRUMBS]->appendToItem("user.admin.edit",$user->getRealname());
        } else {
            $this->flashMessage(
                ProfileException::MESSAGE_NOT_FOUND,
                self::STATUS_DANGER,
                null,
                self::ICON_DANGER,
                100);
        }
        return;
    }

    /**
     * users admin form factory
     * @return UsersAdminForm
     */
    public function createComponentUsersAdminForm()
    {
        return $this->usersAdminFormFactory->create();
    }

    /**
     * user admins grid factory
     * @return UserAdminsGrid
     */
    public function createComponentUsersAdminGrid()
    {
        return $this->usersAdminGridFactory->create();
    }

    /**
     * user history grid factory
     * @return UserHistoryGrid
     */
    public function createComponentUserHistoryGrid()
    {
        return $this->userHistoryGridFactory->create();
    }

    /**
     * user settings grid
     * @return UserSettingsGrid
     */
    public function createComponentUserSettingsGrid()
    {
        return $this->userSettingsGridFactory->create();
    }

    /**
     * user roles grid factory
     * @return UserRolesGrid
     */
    public function createComponentUserRolesGrid()
    {
        return $this->userRolesGridFactory->create();
    }
}