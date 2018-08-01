<?php
namespace occ2\GridControl;

use Ublaboo\DataGrid\DataGrid;
use Nette\Utils\ArrayHash;

/**
 * IAdditionalGridBuilder inteface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
interface IAdditionalGridBuilder
{
    public function __construct($object, DataGrid $grid, GridConfig $configurator, ArrayHash $callbacks);
    public function build();
}
