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
    protected $control;

    /**
     * @var DataGrid
     */
    protected $datagrid;

    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var string | null
     */
    protected $event;

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

    public function getControl(): Control
    {
        return $this->control;
    }

    public function getDatagrid(): DataGrid
    {
        return $this->datagrid;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getEvent()
    {
        return $this->event;
    }



}