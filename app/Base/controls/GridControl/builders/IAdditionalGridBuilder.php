<?php
namespace app\Base\controls\GridControl\builders;

use app\Base\controls\GridControl\GridControl;
use Ublaboo\DataGrid\DataGrid;
use Nette\Utils\ArrayHash;

/**
 * IAdditionalGridBuilder inteface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
interface IAdditionalGridBuilder
{
    /**
     * @param GridControl $object
     * @param DataGrid $grid
     * @param \app\Base\controls\GridControl\builders\GridConfig $configurator
     * @param ArrayHash $callbacks
     */
    public function __construct(GridControl $object, DataGrid $grid, GridConfig $configurator, ArrayHash $callbacks);

    /**
     * build
     */
    public function build();
}
