<?php
namespace app\Base\traits;

use Contributte\EventDispatcher\EventDispatcher;
use app\Base\events\Event;

/**
 * TEvents
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
trait TEvents
{
    /**
     * @var EventDispatcher
     */
    protected $ed;

    /**
     * set event dispatcher
     * @param EventDispatcher $eventDispatcher
     * @return $this
     */
    public function setEventDispatcher(EventDispatcher $eventDispatcher)
    {
        $this->ed = $eventDispatcher;
        return $this;
    }

    /**
     * fire event
     * @param string $eventName
     * @param Event $event
     * @return mixed
     */
    public function on(string $eventName, Event $event)
    {
        return $this->ed->dispatch($eventName, $event);
    }
}