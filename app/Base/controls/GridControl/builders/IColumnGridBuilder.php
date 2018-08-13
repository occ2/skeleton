<?php
namespace app\Base\controls\GridControl\builders;

use app\Base\controls\GridControl\GridControl;
use app\Base\controls\GridControl\DataGrid;
use Nette\Reflection\Property;

/**
 * IColumnGridBuilder inteface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
interface IColumnGridBuilder
{
    /**
     * @param GridControl $object
     * @param DataGrid $grid
     * @param Property $property
     * @param array $callbacks
     */
    public function __construct(GridControl $object, DataGrid $grid, Property $property,array  $callbacks);

    /**
     * build
     */
    public function build();
}