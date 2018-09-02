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
use Contributte\Logging\ILogger;
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
     * @label user.userHistoryGrid.column.id
     * @filter (type=range)
     * @hidden
     */
    public $id;
    
    /**
     * @type datetime
     * @label user.userHistoryGrid.column.datetime
     * @filter (type=date)
     * @sortable
     */
    public $datetime;
    
    /**
     * @type text
     * @label user.userHistoryGrid.column.type
     * @filter (type=select,translateOptions=true)
     */
    public $type;
    
    /**
     * @type text
     * @label user.userHistoryGrid.column.message
     * @translate
     */
    public $message;

    /**
     * startup of control
     * @return void
     */
    public function startup()
    {
        parent::startup();
        $classes = [
            ILogger::INFO=>"success",
            ILogger::WARNING=>"warning",
            ILogger::CRITICAL=>"danger",
            ILogger::EXCEPTION=>"danger"
        ];

        $texts = [
            ILogger::INFO=>$this->_("base.logger.status.info"),
            ILogger::WARNING=>$this->_("base.logger.status.warning"),
            ILogger::CRITICAL=>$this->_("base.logger.status.danger"),
            ILogger::EXCEPTION=>$this->_("base.logger.status.exception")
        ];
        // set custom renderer
        $this->setColumnRendererCallback(self::TYPE, function ($item, GridControl $control) use ($classes,$texts) {

            return Html::el("div")
                   ->addAttributes(["class"=>"badge badge-" . $classes[$control->getEntityProperty($item,self::TYPE)]])
                   ->addText($texts[$control->getEntityProperty($item,self::TYPE)]);
        });
        
        $this->setRowCallback(function($item, $tr, $control) use ($classes) {
            $tr->addClass("alert alert-" . $classes[$control->getEntityProperty($item,self::TYPE)]);
        });

        $this->setLoadOptionsCallback(self::TYPE,function($control) use ($texts){
            return $texts;
        });
        return;
    }
}
