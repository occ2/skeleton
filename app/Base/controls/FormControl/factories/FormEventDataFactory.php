<?php
namespace app\Base\controls\FormControl\factories;

use app\Base\controls\FormControl\events\FormEventData;
use app\Base\controls\FormControl\interfaces\IEventDataFactory;
use Nette\Forms\Container as Form;
use Nette\Application\UI\Control;

/**
 * FormEventDataFactory
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class FormEventDataFactory implements IEventDataFactory
{
    /**
     * create new data object and return
     * @param Form $form
     * @param Control $control
     * @param string $event
     * @return FormEventData
     */
    public function create(Form $form, Control $control, $event = null)
    {
        return new FormEventData($form, $control, $event);
    }
}