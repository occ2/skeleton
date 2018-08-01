<?php
namespace app\Base\controls\FormControl\builders;

use Nette\Application\UI\Form;
use Nette\Application\UI\Control;

/**
 * IFormBuilder inteface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
interface IFormBuilder
{
    /**
     * set FormControl object
     * @param Control $object
     */
    public function setObject(Control $object);

    /**
     * set arry of options callbacks
     * @param array $optionsCallbacks
     */
    public function setOptionsCallbacks(array $optionsCallbacks);

    /**
     * build form
     * @param Form $form
     */
    public function build(Form $form):Form;
}
