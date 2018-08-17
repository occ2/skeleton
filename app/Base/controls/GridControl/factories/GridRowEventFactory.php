<?php
namespace app\Base\controls\GridControl\factories;

use app\Base\controls\GridControl\interfaces\IGridRowEventFactory;
use app\Base\controls\GridControl\events\GridRowEventData;
use app\Base\controls\GridControl\DataGrid;
use Nette\Application\UI\Control;

/**
 * GridRowEventDataFactory
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class GridRowEventFactory implements IGridRowEventFactory
{
    /**
     * create row event data object
     * @param int | string $id
     * @param mixed $data
     * @param DataGrid $datagrid
     * @param Control $control
     * @param string $event
     * @return GridRowEventData
     */
    public function create(
        $id,
        $data,
        DataGrid $datagrid,
        Control $control,
        string $event = null
    ) : GridRowEventData
    {
        return new GridRowEventData(
            $id,
            $data,
            $datagrid,
            $control,
            $event
        );
    }
}