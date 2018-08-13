<?php
namespace app\Base\controls\GridControl\builders;

use app\Base\controls\GridControl\GridControl;
use app\Base\controls\GridControl\configurators\GridColumnsConfig;
use Ublaboo\DataGrid\Column\Column;

/**
 * IColumnGridBuilder inteface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
interface IFilterGridBuilder
{
    /**
     * @param GridControl $object
     * @param Column $column
     * @param GridColumnsConfig $config
     * @param array $callbacks
     */
    public function __construct(GridControl $object, Column $column, GridColumnsConfig $config, array $callbacks);

    /**
     * build
     */
    public function build();
}