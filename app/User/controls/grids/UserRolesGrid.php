<?php
namespace occ2\inventar\User\controls\grids;

use occ2\GridControl\GridControl;

/**
 * UsersGrid
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 * @columnsHidable
 * @title user.userRolesGrid.title
 * @pagination false
 *
 * @inlineActions true
 * @inlineAdd (topPosition=true)
 * inlineEdit 
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
