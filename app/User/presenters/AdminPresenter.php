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
          ACTION_RESET=":User:Admin:reset";

    /**
     * @inject
     * @var \app\User\models\facades\AdminFacade
     */
    public $adminFacade;

    /**
     * @inject
     * @var \app\User\controls\factories\IUsersAdminGrid
     */
    public $userAdminGridFactory;

    /**
     * @var int
     */
    private $id;

    /**
     * @title user.navbar.administration
     */
    public function actionDefault()
    {

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

    public function actionAdd()
    {
    }

    public function actionEdit($id)
    {
        $this->id = $id;
    }

    public function actionReset($id)
    {
        $this->id = $id;
    }

    public function createComponentUsersForm()
    {

    }

    public function createComponentUsersAdminGrid()
    {
        $grid = $this->userAdminGridFactory->create();
        $grid->setDatasource($this->adminFacade->load());
        return $grid;
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