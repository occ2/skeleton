<?php
namespace app\Base\controls\GridControl\builders;

use app\Base\controls\GridControl\GridControl;
use app\Base\controls\GridControl\DataGrid;

/**
 * IGridBuilder interface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
interface IGridBuilder
{
    /**
     * set GridControl parent object reference
     * @param GridControl $object
     */
    public function setObject(GridControl $object);
    
    /**
     * build datagrid
     * @param DataGrid $grid
     */
    public function build(DataGrid $grid): DataGrid;
}
