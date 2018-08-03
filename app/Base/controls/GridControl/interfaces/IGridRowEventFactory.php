<?php
namespace app\Base\controls\GridControl\interfaces;

use app\Base\controls\GridControl\events\GridRowEventData;
use Ublaboo\DataGrid\DataGrid;
use Nette\Application\UI\Control;

/**
 * IGridRowEventFactory inteface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
interface IGridRowEventFactory
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
        string $event=null
    );
}