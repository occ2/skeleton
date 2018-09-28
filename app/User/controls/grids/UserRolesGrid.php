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
use app\User\models\facades\AuthorizationFacade;

/**
 * UserRolesGrid
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 * @columnsHidable
 * @title user.userRolesGrid.title
 * @pagination false
 *
 * @inlineActions true
 * @inlineAdd (topPosition=true,event="User.UserRolesGrid.add.onSuccess")
 * @inlineFormControl (name="role",type="select")
 * @action (name="delete",event="User.UserRolesGrid.delete.onConfirm",title="user.userRolesGrid.action.delete",icon="trash",confirm="user.userRolesGrid.confirm.delete",class="ajax btn btn-xs btn-danger")
 */
final class UserRolesGrid extends GridControl
{
    const ID="id",
          USER="Users_id",
          ROLE="role",
          COMMENT="comment",
          ACTION_DELETE="delete",
          EVENT_SUCCESS_ADD="User.UserRolesGrid.add.onSuccess",
          EVENT_DELETE_CONFIRM="User.UserRolesGrid.delete.onConfirm",
          ACL_RESOURCE="users";
    
    /**
     * @label user.userRolesGrid.column.id
     * @type number
     * @hidden true
     */
    public $id;

    /**
     * @label user.userRolesGrid.column.role
     * @type text
     */
    public $role;

    /**
     * @label user.userRolesGrid.column.comment
     * @type text
     * @dbCol role
     */
    //public $comment;

    public function startup()
    {
        $t = $this;
        // load values into inline form select box
        $this->setInlineLoadOptionsCallback(self::ROLE,function($control) use ($t){
            $roles = [];
            $arr = $t->getPresenter()
                     ->getUser()
                     ->getAuthorizator()
                     ->getRoles();
            foreach ($arr as $value){
                $roles[$value] = $value;
            }
            return $roles;
        });
        // setup allow action condition
        $this->setAllowRowsActionCallback(self::ACTION_DELETE,function($item,$control) use($t){
            return $t->getPresenter()
                     ->getUser()
                     ->isAllowed(self::ACL_RESOURCE, AuthorizationFacade::PRIVILEGE_WRITE);
        });
        // setup allow inline add condition
        $this->setAllowInlineAddCallback(function($control) use ($t){
            return $t->getPresenter()
                     ->getUser()
                     ->isAllowed(self::ACL_RESOURCE, AuthorizationFacade::PRIVILEGE_WRITE);
        });
    }
}
