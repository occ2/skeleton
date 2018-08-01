<?php
namespace occ2\FormControl;

use Nette\Application\UI\Form;

/**
 * IFormBuilder inteface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
interface IFormBuilder
{
    public function setObject($object);
    public function setOptionsCallbacks(array $optionsCallbacks);
    public function build(Form $form):Form;
}
