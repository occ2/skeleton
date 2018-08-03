<?php
namespace app\Base\controls\FormControl\interfaces;

use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;

/**
 * IFormFactory inteface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
interface IFormFactory
{
    /**
     * @return Form
     */
    public function create();

    /**
     * @return ITranslator
     */
    public function getTranslator();
}