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
     * @var string
     */
    public $event;
    
    /**
     * @var DataGrid
     */
    public $datagrid;

    /**
     * @var Form
     */
    public $form;

    /**
     * @var Control
     */
    public $control;

    /**
     * @var mixed
     */
    public $data;

    /**
     * @param mixed $data
     * @param string $event
     * @return type
     */
    public function __construct(DataGrid $datagrid, Control $control, Form $form=null, $event=null, $data=null)
    {
        $this->datagrid = $datagrid;
        $this->event = $event;
        $this->form = $form;
        $this->control = $control;
        $this->data = $data;
        return;
    }
}
