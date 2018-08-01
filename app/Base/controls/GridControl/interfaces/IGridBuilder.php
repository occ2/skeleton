<?php
namespace occ2\GridControl;

use Ublaboo\DataGrid\DataGrid;
use Nette\Utils\ArrayHash;

/**
 * IGridBuilder interface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
interface IGridBuilder
{
    /**
     * set GridControl parent object reference
     * @param \occ2\GridControl\GridControl $object
     */
    public function setObject(GridControl $object);
    
    /**
     * build datagrid
     * @param DataGrid $grid
     */
    public function build(DataGrid $grid): DataGrid;
}
