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

namespace app\User\controls\grids;

use app\Base\controls\GridControl\GridControl;

/**
 * UsersGrid
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 * @columnsHidable
 * @outerFilter true
 * @title user.usersAdminGrid.title
 *
 * @toolbarButton (name="add", title="user.usersAdminGrid.action.add", href="add",icon="user-plus",class="ajax btn btn-xs btn-primary")
 * @action (name="edit",href="edit",title="user.usersAdminGrid.action.edit",icon="edit",class="btn-dark ajax")
 * @action (name="history",href="history",title="user.usersAdminGrid.action.history",icon="history",class="btn-dark ajax")
 * @action (name="settings",href="settings",title="user.usersAdminGrid.action.settings",icon="cogs",class="btn-dark ajax")
 * @action (name="roles",href="roles",title="user.usersAdminGrid.action.roles",icon="users",class="btn-dark ajax")
 * @action (name="reset",title="user.usersAdminGrid.action.reset",icon="unlock-alt",class="btn-dark ajax",confirmCol="realname",confirm="user.usersAdminGrid.confirm.reset")
 * @action (name="delete",title="user.usersAdminGrid.action.delete",icon="trash",class="btn-danger ajax",confirmCol="realname",confirm="user.usersAdminGrid.confirm.delete")
 */
final class UsersAdminGrid extends GridControl
{
    const ID="id",
          USERNAME="username",
          REALNAME="realname",
          EMAIL="email",
          PHONE="phone",
          QUESTION="cQuestion",
          SECRET="secret",
          STATUS="status",
          TOOLBAR_BUTTON_ADD="add",
          ACTION_EDIT="edit",
          ACTION_HISTORY="history",
          ACTION_SETTINGS="settings",
          ACTION_ROLES="roles",
          ACTION_RESET="reset",
          ACTION_DELETE="delete",
          EVENT_DELETE="User.UserAdminGrid.delete.onConfirm",
          EVENT_RESET="User.UserAdminGrid.reset.onConfirm",
          STATUSES=[
              0=>"user.usersAdminGrid.column.status.inactive",
              1=>"user.usersAdminGrid.column.status.active"
          ];
    
    /**
     * @label user.usersAdminGrid.column.id
     * @type number
     * @filter (type=text)
     * @hidden true
     */
    public $id;
    
    /**
     * @label user.usersAdminGrid.column.username
     * @type text
     * @filter (type=text)
     */
    public $username;

    /**
     * @label user.usersAdminGrid.column.realname
     * @type text
     * @filter (type=text)
     */
    public $realname;

    /**
     * @label user.usersAdminGrid.column.email
     * @type text
     * @filter (type=text)
     */
    public $email;

    /**
     * @label user.usersAdminGrid.column.phone
     * @type text
     * @filter (type=text)
     */
    public $phone;

    /**
     * @label user.usersAdminGrid.column.question
     * @type text
     * @filter (type=text)
     * @hidden true
     */
    public $cQuestion;

    /**
     * @label user.usersAdminGrid.column.secret
     * @type text
     * @hidden true
     */
    public $secret;

    /**
     * @label user.usersAdminGrid.column.status.title
     * @type status
     * @filter (type=select)
     * @option (text='user.usersAdminGrid.column.status.inactive',class='ajax btn btn-xs btn-danger', classInDropdown="ajax dropdown-item")
     * @option (text='user.usersAdminGrid.column.status.active',class='ajax btn btn-xs btn-success', classInDropdown="ajax dropdown-item")
     */
    public $status;

    public function startup()
    {
        parent::startup();
        $this->setStatusChangeCallback(self::STATUS,function($id,$value,$control){

        });
        $this->setLoadOptionsCallback(self::STATUS,function($control){
            return [
                0=>$this->_(self::STATUSES[0]),
                1=>$this->_(self::STATUSES[1]),
            ];
        });
    }
}
