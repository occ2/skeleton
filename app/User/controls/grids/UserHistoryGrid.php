<?php
namespace occ2\inventar\User\controls\grids;

use occ2\GridControl\GridControl;

/**
 * UserHistoryGrid
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
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
    
    public function startup()
    {
        $classes = [
                1=>"info",
                2=>"success",
                3=>"warning",
                4=>"danger",
                5=>"danger"
            ];
        $this->setColumnRendererCallback("type", function ($item, $control) use ($classes) {
            $texts = [
                1=>$control->text("base.logger.status.info"),
                2=>$control->text("base.logger.status.success"),
                3=>$control->text("base.logger.status.warning"),
                4=>$control->text("base.logger.status.danger"),
                5=>$control->text("base.logger.status.exception")
            ];
            return \Nette\Utils\Html::el("div")
                   ->addAttributes(["class"=>"badge badge-" . $classes[$item->type]])
                   ->addText($texts[$item->type]);
        });
        $this->setRowCallback(function ($item, $tr, $control) use ($classes) {
            $tr->addClass("alert alert-" . $classes[$item->type]);
        });
        return parent::startup();
    }
}
