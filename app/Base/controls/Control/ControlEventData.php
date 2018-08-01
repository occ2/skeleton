<?php
namespace app\Base\controls\Control;

use app\Base\events\Event;
use Nette\Application\UI\Control;

/**
 * ControlEventData
 *
 * extend base event to be a container on Presenter and Control object
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class ControlEventData extends Event
{
    /**
     * @var \Nette\Application\UI\Control
     */
    public $control;

    /**
     * @param array | ArryHash $data
     * @param Control $control
     * @param string $event
     * @return type
     */
    public function __construct($data, Control $control=null, string $event = null)
    {
        $this->control = $control;
        parent::__construct($data, $event);
        return;
    }
}