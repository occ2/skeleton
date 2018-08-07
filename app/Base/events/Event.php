<?php
namespace app\Base\events;

use Contributte\EventDispatcher\Events\AbstractEvent;

/**
 * event container for all events
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
abstract class Event extends AbstractEvent
{
    /**
     * @var array
     */
    public $data;

    /**
     * @var string
     */
    public $event;

    /**
     * @param mixed $data
     * @param string $event
     * @return type
     */
    public function __construct($data, $event=null)
    {
        $this->data = $data;
        $this->event = $event;
        return;
    }

    /**
     * data getter
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }
}
