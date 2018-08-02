<?php
namespace app\Base\controls\GridControl\builders;

use app\Base\controls\GridControl\GridControl;
use app\Base\controls\GridControl\configurators\GridConfig;
use app\Base\controls\GridControl\DataGrid;
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
     * @param GridConfig $configurator
     * @param array $callbacks
     */
    public function __construct(GridControl $object, DataGrid $grid, GridConfig $configurator, ArrayHash $callbacks);

    /**
     * build
     */
    public function build();
}
