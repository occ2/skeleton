<?php
namespace occ2\inventar\User\controls\grids;

use occ2\GridControl\GridControl;

/**
 * UsersGrid
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 * @columnsHidable
 * @title user.usersGrid.title
 *
 * @toolbarButton (name="add", title="user.usersGrid.add", href="add",icon="user-plus",class="ajax btn btn-xs btn-primary")
 * @action (name="edit",href="edit",title="user.usersGrid.edit",icon="edit",class="btn-dark ajax")
 * @action (name="history",href="history",title="user.usersGrid.history",icon="history",class="btn-dark ajax")
 * @action (name="settings",href="settings",title="user.usersGrid.settings",icon="cogs",class="btn-dark ajax")
 * @action (name="roles",href="roles",title="user.usersGrid.roles",icon="users",class="btn-dark ajax")
 * @action (name="reset",href=":resetPassword!",title="user.usersGrid.reset",icon="unlock-alt",class="btn-dark ajax",confirmCol="realname",confirm="user.usersGrid.confirmReset")
 * @action (name="delete",href=":delete!",title="user.usersGrid.delete",icon="trash",class="btn-danger ajax",confirmCol="realname",confirm="user.usersGrid.confirmDelete")
 */
final class UsersGrid extends GridControl
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
          ACTION_DELETE="delete";
    
    /**
     * @label user.usersGrid.id
     * @type number
     * @filter (type=text)
     * @hidden true
     */
    public $id;
    
    /**
     * @label user.usersGrid.username
     * @type text
     * @filter (type=text)
     */
    public $username;

    /**
     * @label user.usersGrid.realname
     * @type text
     * @filter (type=text)
     */
    public $realname;

    /**
     * @label user.usersGrid.email
     * @type text
     * @filter (type=text)
     */
    public $email;

    /**
     * @label user.usersGrid.phone
     * @type text
     * @filter (type=text)
     */
    public $phone;

    /**
     * @label user.usersGrid.question
     * @type text
     * @filter (type=text)
     * @hidden true
     */
    public $cQuestion;

    /**
     * @label user.usersGrid.secret
     * @type text
     * @hidden true
     */
    public $secret;

    /**
     * @label user.usersGrid.status
     * @type status
     * @filter (type=select)
     * @option (text='user.usersGrid.inactive',class='ajax btn btn-xs btn-danger', classInDropdown="ajax dropdown-item")
     * @option (text='user.usersGrid.active',class='ajax btn btn-xs btn-success', classInDropdown="ajax dropdown-item")
     */
    public $status;
}
