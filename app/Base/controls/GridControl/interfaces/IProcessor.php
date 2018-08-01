<?php
namespace occ2\GridControl;

use occ2\GridControl\GridControl;
use Nette\Application\UI\Presenter;

/**
 * IProcessor inteface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
interface IProcessor
{
    public function process(GridControl $grid, Presenter $presenter);
}