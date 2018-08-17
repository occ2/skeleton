<?php
namespace app\Base\controls\GridControl\events;

use app\Base\controls\GridControl\DataGrid;
use Contributte\EventDispatcher\Events\AbstractEvent as BaseEvent;
use Nette\Forms\Container as Form;
use Nette\Application\UI\Control;

/**
 * grid event container
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class GridEventData extends BaseEvent
{

    /**
     * @var string | null
     */
    protected $event;
    
    /**
     * @var DataGrid
     */
    protected $datagrid;

    /**
     * @var Form | null
     */
    protected $form;

    /**
     * @var Control
     */
    protected $control;

    /**
     * @var mixed | null
     */
    protected $data;

    /**
     * @param mixed $data
     * @param string $event
     * @return void
     */
    public function __construct(DataGrid $datagrid, Control $control, Form $form=null, $data=null, $event=null)
    {
        $this->datagrid = $datagrid;
        $this->event = $event;
        $this->form = $form;
        $this->control = $control;
        $this->data = $data;
        return;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function getDatagrid(): DataGrid
    {
        return $this->datagrid;
    }

    public function getForm(): ?Form
    {
        return $this->form;
    }

    public function getControl(): Control
    {
        return $this->control;
    }

    public function getData()
    {
        return $this->data;
    }
}
