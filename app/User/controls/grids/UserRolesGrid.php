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
 * UserRolesGrid
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 * @columnsHidable
 * @title user.userRolesGrid.title
 * @pagination false
 *
 * @inlineActions true
 * @inlineAdd (topPosition=true)
 * @inlineFormControl (name="role",type="select")
 *
 * @action (name="delete",title="user.userRolesGrid.delete",icon="trash",confirm="user.userRolesGrid.confirmDelete",class="ajax btn btn-xs btn-danger")
 */
final class UserRolesGrid extends GridControl
{
    const ID="id",
          USER="Users_id",
          ROLE="role",
          COMMENT="comment",
          ACTION_DELETE="delete";
    
    /**
     * @label user.userRolesGrid.id
     * @type number
     * @hidden true
     */
    public $id;

    /**
     * @label user.userRolesGrid.role
     * @type text
     */
    public $role;

    /**
     * @label user.userRolesGrid.comment
     * @type text
     * @dbCol role
     */
    public $comment;
}
