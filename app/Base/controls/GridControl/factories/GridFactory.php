<?php
namespace app\Base\controls\GridControl\factories;

use app\Base\controls\GridControl\interfaces\IGridFactory;
use app\Base\controls\GridControl\DataGrid;

/**
 * GridFactory
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class GridFactory implements IGridFactory
{
    /**
     * create datagrid object
     * @return DataGrid
     */
    public function create() : DataGrid
    {
        return new DataGrid();
    }

    /**
     * get datagrid object class
     * @return string
     */
    public function getClass() : string
    {
        return DataGrid::class;
    }
}