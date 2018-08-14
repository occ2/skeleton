<?php
namespace app\Base\controls\FormControl\interfaces;

use Contributte\EventDispatcher\Events\AbstractEvent as BaseEvent;
use Nette\Forms\Container as Form;
use Nette\Application\UI\Control;

/**
 * IFactory inteface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
interface IEventFactory
{
    /**
     * create event data container
     * @param Form $form
     * @param Control $control
     * @param string $event
     * @return BaseEvent
     */
    public function create(Form $form, Control $control, $event=null);
}