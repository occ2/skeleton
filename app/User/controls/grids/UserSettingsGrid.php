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
use app\User\models\entities\Settings;
use app\Base\controls\GridControl\events\GridEventData;

/**
 * UserSettingsGrid
 * datagrid of user application custom settings
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 * @columnsHidable
 * @title user.userSettingsGrid.title
 * @defaultPerPage 50
 * @toolbarButton (name="reload", event="User.SettingsGrid.reload.onClick", title="user.userSettingsGrid.action.reload",icon="retweet",class="ajax btn btn-xs btn-primary")
 * @toolbarButton (name="reset", event="User.SettingsGrid.reset.onClick", title="user.userSettingsGrid.action.reset",icon="eraser",class="ajax btn btn-xs btn-danger")
 * 
 */
final class UserSettingsGrid extends GridControl
{
    const ID="id",
          COMMENT="comment",
          KEY="key",
          VALUE="value",
          TOOLBAR_BUTTON_RESET="reset",
          TOOLBAR_BUTTON_RELOAD="reload",
          EVENT_CLICK_RESET="User.SettingsGrid.reset.onClick",
          EVENT_CLICK_RELOAD="User.SettingsGrid.reload.onClick",
          EVENT_EDIT_VALUE="User.SettingsGrid.value.onSubmit";

    const YES_NO=[
        0=>"base.shared.no",
        1=>"base.shared.yes"
    ];
    
    /**
     * @label user.userSettingsGrid.column.id
     * @type number
     * @hidden true
     */
    public $id;

    /**
     * @label user.userSettingsGrid.column.type
     * @type text
     * @hidden true
     */
    public $type;
    
    /**
     * @label user.userSettingsGrid.column.comment
     * @type text
     * @translate
     */
    public $comment;

    /**
     * @label user.userSettingsGrid.column.key
     * @type text
     * @hidden true
     */
    public $key;

    /**
     * @label user.userSettingsGrid.column.value
     * @type text
     * @editableType text
     */
    public $value;
    
    public function startup()
    {
        parent::startup();
        $t = $this;
        $this->setColumnRendererCallback(self::VALUE, function ($item, GridControl $control){
            if($control->getEntityProperty($item, Settings::TYPE)=="bool"){
                $a = self::YES_NO;
                return $control->_($a[$control->getEntityProperty($item, Settings::VALUE)]);
            } else {
                return $control->getEntityProperty($item, Settings::VALUE);
            }
        });
        $this->setEditableCallback(self::VALUE,function($id,$value,$control) use ($t){
            $data = new GridEventData($control[static::GRID_CONTROL], $control, null, [self::ID=>$id,self::VALUE=>$value] , self::EVENT_EDIT_VALUE);
            $this->on(self::EVENT_EDIT_VALUE, $data);
            return;
        });
    }
}
