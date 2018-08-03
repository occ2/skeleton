<?php
namespace app\Base\controls\GridControl\interfaces;

use Ublaboo\DataGrid\DataGrid;

/**
 * IGridFactory interface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
interface IGridFactory
{
    /**
     * create datagrid object
     * @return DataGrid
     */
    public function create();

    /**
     * get datagrid pobject class name
     * @return string
     */
    public function getClass() : string;
}