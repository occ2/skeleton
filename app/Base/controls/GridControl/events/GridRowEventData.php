<?php
namespace app\Base\controls\GridControl\events;

use app\Base\controls\GridControl\DataGrid;
use Contributte\EventDispatcher\Events\AbstractEvent as BaseEvent;
use Nette\Application\UI\Control;

/**
 * GridRowEventData
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class GridRowEventData extends BaseEvent
{
    /**
     * @var Control
     */
    public $control;

    /**
     * @var DataGrid
     */
    public $datagrid;

    /**
     * @var mixed
     */
    public $id;

    /**
     * @var mixed
     */
    public $data;

    /**
     * @var string
     */
    public $event;

    /**
     * @param mixed $id
     * @param mixed $data
     * @param DataGrid $datagrid
     * @param Control $control
     * @param string $event
     * @return void
     */
    public function __construct($id,$data,DataGrid $datagrid,Control $control,string $event=null)
    {
        $this->id = $id;
        $this->data = $data;
        $this->datagrid = $datagrid;
        $this->control = $control;
        $this->event = $event;
        return;
    }
}