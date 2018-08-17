<?php
namespace app\Base\events;

use Contributte\EventDispatcher\Events\AbstractEvent;
use Nette\Utils\ArrayHash;

/**
 * event container for all events
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
abstract class Event extends AbstractEvent
{
    const ENTITY="entity",
          REPOSITORY="repository",
          COLLECTION="collection",
          QUERY_BUILDER="queryBuilder";
    
    /**
     * @var array | ArrayHash
     */
    private $data;

    /**
     * @var string | null
     */
    private $event;

    /**
     * @param array | ArrayHash $data
     * @param string | null $event
     * @return void
     */
    public function __construct($data, string $event=null)
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

    /**
     * get event name
     * @return string | null
     */
    public function getEvent()
    {
        return $this->event;
    }
}
