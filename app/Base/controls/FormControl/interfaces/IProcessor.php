<?php
namespace app\Base\controls\FormControl\interfaces;

use app\Base\controls\FormControl;
use Nette\Application\UI\Presenter;

/**
 * IProcessor inteface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
interface IProcessor
{
    /**
     * additional process of form
     * @param FormControl $form
     * @param Presenter $presenter
     */
    public function process(FormControl $form, Presenter $presenter);
}