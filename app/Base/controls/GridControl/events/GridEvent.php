<?php
namespace occ2\GridControl;

use Contributte\EventDispatcher\Events\AbstractEvent;
use Nette\Forms\Container as Form;
use Nette\Application\UI\Presenter;
use Ublaboo\DataGrid\DataGrid;

/**
 * grid event container
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class GridEvent extends AbstractEvent
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
     * @var \Nette\Application\UI\Presenter
     */
    public $presenter;

    /**
     * @param mixed $data
     * @param string $event
     * @return type
     */
    public function __construct(DataGrid $datagrid, Presenter $presenter, Form $form=null, $event=null)
    {
        $this->datagrid = $datagrid;
        $this->event = $event;
        $this->form = $form;
        $this->presenter = $presenter;
        return;
    }
}
