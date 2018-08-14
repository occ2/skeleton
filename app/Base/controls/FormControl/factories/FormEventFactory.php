<?php
namespace app\Base\controls\FormControl\factories;

use app\Base\controls\FormControl\events\FormEvent;
use app\Base\controls\FormControl\interfaces\IEventFactory;
use Nette\Forms\Container as Form;
use Nette\Application\UI\Control;

/**
 * FormEventFactory
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class FormEventFactory implements IEventFactory
{
    /**
     * create new data object and return
     * @param Form $form
     * @param Control $control
     * @param string $event
     * @return FormEvent
     */
    public function create(Form $form, Control $control, $event = null)
    {
        return new FormEvent($form, $control, $event);
    }
}