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
 * UserSettingsGrid
 * datagrid of user application custom settings
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 * @columnsHidable
 * @title user.userConfigGrid.title
 * @defaultPerPage 50
 * @toolbarButton (name="reload", title="user.userConfigGrid.reload",icon="retweet",class="ajax btn btn-xs btn-primary")
 * @toolbarButton (name="reset", title="user.userConfigGrid.reset",icon="eraser",class="ajax btn btn-xs btn-danger")
 */
final class UserSettingsGrid extends GridControl
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
