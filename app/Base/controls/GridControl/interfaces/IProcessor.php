<?php
namespace app\Base\controls\GridControl\interfaces;

use app\Base\controls\GridControl\GridControl;
use Nette\Application\UI\Presenter;

/**
 * IProcessor interface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
interface IProcessor
{
    /**
     * process additional datagerid settings
     * @param GridControl $grid
     * @param Presenter $presenter
     */
    public function process(GridControl $grid, Presenter $presenter);
}