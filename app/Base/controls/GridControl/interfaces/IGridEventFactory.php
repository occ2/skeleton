<?php
namespace app\Base\controls\GridControl\interfaces;

use app\Base\controls\GridControl\DataGrid;
use Nette\Application\UI\Control;
use Nette\Forms\Container as Form;
use app\Base\controls\GridControl\events\GridEventData;

/**
 * IGridEventFactory inteface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
interface IGridEventFactory
{
    /**
     * create event data container
     * @param DataGrid $datagrid
     * @param Control $control
     * @param Form $form
     * @param mixed $data
     * @param string $event
     * @return GridEventData
     */
    public function create(
        DataGrid $datagrid,
        Control $control,
        Form $form=null,
        $data=null,
        $event=null
    );
}