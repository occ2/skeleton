<?php
namespace app\Base\controls\Control;

use app\Base\events\Event;
use Nette\Application\UI\Control;
use Nette\Utils\ArrayHash;

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
     * @var Control | null
     */
    public $control;

    /**
     * @param array | ArrayHash $data
     * @param Control $control
     * @param string $event
     * @return void
     */
    public function __construct($data, Control $control=null, string $event = null)
    {
        $this->control = $control;
        parent::__construct($data, $event);
        return;
    }
}