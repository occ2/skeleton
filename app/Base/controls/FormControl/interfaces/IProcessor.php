<?php
namespace occ2\FormControl;

use Nette\Application\UI\Presenter;
use occ2\FormControl\FormControl;

/**
 * IProcessor inteface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
interface IProcessor
{
    public function process(FormControl $form, Presenter $presenter);
}