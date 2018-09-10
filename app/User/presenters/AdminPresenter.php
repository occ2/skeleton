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

/**
 * AdminPresenter
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 * @todo SET ACL !!
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
          USERS_FORM="usersAdminForm";

    /**
     * @inject
     * @var \app\User\models\facades\AdminFacade
     */
    public $adminFacade;

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
     * @var int
     */
    private $id=null;

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

    public function actionHistory($id)
    {
        $this->id = $id;
    }

    public function actionSettings($id)
    {
        $this->id = $id;
    }

    public function actionRoles($id)
    {
        $this->id = $id;
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
        if($this->id!=null){
            $user = $this->adminFacade->get($this->id, true);
            $this->appendToTitle($user->realname);
            $this[self::USERS_FORM]->setDefaults($user);
            $this[self::USERS_FORM]->appendToTitle($user->realname);
        }
        return;
    }

    public function actionReset($id)
    {
        $this->id = $id;
        // todo
        return;
    }

    public function createComponentUsersAdminForm()
    {
        return $this->usersAdminFormFactory->create();
    }

    public function createComponentUsersAdminGrid()
    {
        return $this->usersAdminGridFactory->create();
    }

    public function createComponentHistoryGrid()
    {

    }

    public function createComponentSettingsGrid()
    {

    }

    public function createComponentRolesGrid()
    {

    }

    public function createComponentResetDialog()
    {

    }
}