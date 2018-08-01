<?php
namespace app\Base\controls\FormControl\events;

use Contributte\EventDispatcher\Events\AbstractEvent as Event;
use Nette\Forms\Container as Form;
use Nette\Application\UI\Control;

/**
 * form event data container
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class FormEventData extends Event
{

    /**
     * @var string
     */
    public $event;

    /**
     * @var \Nette\Forms\Container
     */
    public $form;

    /**
     * @var \Nette\Application\UI\Control
     */
    public $control;

    /**
     * @param mixed $data
     * @param string $event
     * @return type
     */
    public function __construct(Form $form, Control $control, $event=null)
    {
        $this->event = $event;
        $this->form = $form;
        $this->control = $control;
        return;
    }
}
