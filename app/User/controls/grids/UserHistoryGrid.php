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
use Nette\Utils\Html;

/**
 * UserHistoryGrid
 * class to view order and filter users history
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 *
 * @columnsHidable true
 * @title user.userHistoryGrid.title
 * @defaultSort (datetime=DESC)
 *
 * @export (name=text,class="btn btn-xs btn-primary", icon=download)
 */
final class UserHistoryGrid extends GridControl
{
    const ID="id",
          DATETIME="datetime",
          TYPE="type",
          MESSAGE="message";
    
    /**
     * @type number
     * @label ID
     * @filter (type=range)
     * @hidden
     */
    public $id;
    
    /**
     * @type datetime
     * @label user.userHistoryGrid.datetime
     * @filter (type=date)
     * @sortable
     */
    public $datetime;
    
    /**
     * @type text
     * @label user.userHistoryGrid.type
     * @filter (type=select,translateOptions=true)
     */
    public $type;
    
    /**
     * @type text
     * @label user.userHistoryGrid.message
     * @translate
     */
    public $message;

    /**
     * startup of control
     * @return void
     */
    public function startup()
    {
        // log type map
        $classes = [
                1=>"info",
                2=>"success",
                3=>"warning",
                4=>"danger",
                5=>"danger"
            ];

        // set custom renderer
        $this->setColumnRendererCallback("type", function ($item, $control) use ($classes) {
            $texts = [
                1=>$control->text("base.logger.status.info"),
                2=>$control->text("base.logger.status.success"),
                3=>$control->text("base.logger.status.warning"),
                4=>$control->text("base.logger.status.danger"),
                5=>$control->text("base.logger.status.exception")
            ];
            return Html::el("div")
                   ->addAttributes(["class"=>"badge badge-" . $classes[$item->type]])
                   ->addText($texts[$item->type]);
        });
        $this->setRowCallback(function ($item, $tr, $control) use ($classes) {
            $tr->addClass("alert alert-" . $classes[$item->type]);
        });

        parent::startup();
        return;
    }
}
