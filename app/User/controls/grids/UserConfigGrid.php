<?php
namespace occ2\inventar\User\controls\grids;

use occ2\GridControl\GridControl;

/**
 * UsersGrid
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 * @columnsHidable
 * @title user.userConfigGrid.title
 * @defaultPerPage 50
 * @toolbarButton (name="reload", title="user.userConfigGrid.reload",icon="retweet",class="ajax btn btn-xs btn-primary")
 * @toolbarButton (name="reset", title="user.userConfigGrid.reset",icon="eraser",class="ajax btn btn-xs btn-danger")
 */
final class UserConfigGrid extends GridControl
{
    const ID="id",
          COMMENT="comment",
          KEY="key",
          VALUE="value",
          TOOLBAR_BUTTON_RESET="reset",
          TOOLBAR_BUTTON_RELOAD="reload";

    const YES_NO=[
        0=>"base.shared.no",
        1=>"base.shared.yes"
    ];
    
    /**
     * @label user.userConfigGrid.id
     * @type number
     * @hidden true
     */
    public $id;

    /**
     * @label user.userConfigGrid.type
     * @type text
     * @hidden true
     */
    public $type;
    
    /**
     * @label user.userConfigGrid.comment
     * @type text
     * @translate
     */
    public $comment;

    /**
     * @label user.userConfigGrid.key
     * @type text
     * @hidden true
     */
    public $key;

    /**
     * @label user.userConfigGrid.value
     * @type text
     */
    public $value;
}
