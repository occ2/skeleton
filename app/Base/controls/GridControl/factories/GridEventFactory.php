<?php
namespace app\Base\controls\GridControl\factories;

use app\Base\controls\GridControl\interfaces\IGridEventFactory;
use app\Base\controls\GridControl\events\GridEventData;
use app\Base\controls\GridControl\DataGrid;
use Nette\Application\UI\Control;
use Nette\Forms\Container as Form;

/**
 * GridEventFactory
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class GridEventFactory implements IGridEventFactory
{
    /**
     * create grid event data container
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
        Form $form = null,
        $data = null,
        $event = null
    ) : GridEventData
    {
        return new GridEventData($datagrid, $control, $form, $data, $event);
    }
}