<?php
namespace app\Base\controls\GridControl\configurators;

use Nette\Reflection\ClassType;
use Nette\Utils\Strings;
use Nette\Utils\ArrayHash;

/**
 * GridConfig
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class GridConfig
{
    const TEXTS=[
        "title","comment","footer"
    ];

    const CONFIGS=[
        "events"=>"setEvents"
    ];

    /**
     * @var array
     */
    protected $annotations;

    /**
     * @var object
     */
    protected $parent;

    /**
     * @param string $class
     * @param object $parent
     * @return void
     */
    public function __construct(string $class, $parent)
    {
        $this->annotations = ClassType::from($class)->getAnnotations();
        $this->parent = $parent;
        return;
    }

    /**
     * test if method begins on get and then read from annotation and send as ArrayHash
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __call($name, $arguments)
    {
        if (!Strings::startsWith($name, "get")) {
            throw new \BadMethodCallException;
        }
        $anchor = Strings::firstLower(str_replace("get", "", $name));
        
        if (array_key_exists($anchor, static::CONFIGS) && array_key_exists($anchor, $this->annotations)) {
            return $this->parent->{static::CONFIGS[$anchor]}();
        } elseif (in_array($anchor, static::TEXTS) && array_key_exists($anchor, $this->annotations)) {
            return $this->annotations[$anchor][0];
        } elseif (array_key_exists($anchor, $this->annotations)) {
            if (isset($arguments[0]) && $arguments[0]==true) {
                return $this->annotations[$anchor];
            } else {
                return is_array($this->annotations[$anchor][0]) ? ArrayHash::from($this->annotations[$anchor][0]) : $this->annotations[$anchor][0];
            }
        } else {
            return null;
        }
    }
}
