<?php
namespace app\Base\controls\GridControl\events;

use Contributte\EventDispatcher\Events\AbstractEvent as BaseEvent;
use Nette\Forms\Container as Form;
use Nette\Application\UI\Control;
use Ublaboo\DataGrid\DataGrid;

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
     * @var \Ublaboo\DataGrid\DataGrid
     */
    public $datagrid;

    /**
     * @var \Nette\Forms\Container
     */
    public $form;

    /**
     * @var \Nette\Application\UI\Control
     */
    public $control;

    /**
     * @param mixed $data
     * @param string $event
     * @return type
     */
    public function __construct(DataGrid $datagrid, Control $control, Form $form=null, $event=null)
    {
        $this->datagrid = $datagrid;
        $this->event = $event;
        $this->form = $form;
        $this->control = $control;
        return;
    }
}
