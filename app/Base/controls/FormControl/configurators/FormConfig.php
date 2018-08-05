<?php
namespace app\Base\controls\FormControl\configurators;

use app\Base\controls\FormControl\FormControl;
use Nette\Reflection\ClassType;
use Nette\Utils\Strings;
use Nette\Utils\ArrayHash;
use Nette\DI\Config\Helpers;
use Nette\Caching\Cache;

/**
 * FormConfig
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class FormConfig
{
    const CACHE_PREFIX="forms";

    const TEXTS=[
        "title","comment","footer"
    ];

    const CONFIGS=[
        "ajax"=>"setAjax",
        "modal"=>"setModal",
        "events"=>"setEvents"
    ];

    /**
     * @var array
     */
    protected $renderers=[
        "form"=>"rForm",
        "error"=>"rError",
        "group"=>"rGroup",
        "controls"=>"rControls",
        "pair"=>"rPair",
        "control"=>"rControl",
        "label"=>"rLabel",
        "hidden"=>"rHidden"
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
     * @var Cache
     */
    protected $cache;

    /**
     * @param string $class
     * @param object $parent
     * @return void
     */
    public function __construct(FormControl $parent)
    {
        $classType = ClassType::from($parent);
        $this->cache = new Cache($parent->_cacheStorage,static::CACHE_PREFIX);
        $this->annotations = $this->cache->load($classType->getName());
        if($this->annotations===null){
            $this->annotations = $classType->getAnnotations();
            $this->cache->save($classType->getName(), $this->annotations,[
                Cache::FILES => $classType->getFileName()
            ]);
        }
        $this->parent = $parent;
        $this->renderer();
        return;
    }

    /**
     * @return void
     */
    protected function renderer()
    {
        foreach ($this->renderers as $key=>$anchor) {
            if (isset($this->annotations[$anchor])) {
                $this->parent->_rendererWrappers[$key] = Helpers::merge((array) $this->annotations[$anchor][0], $this->parent->_rendererWrappers[$key]);
            }
        }
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
            return (isset($arguments[0]) && $arguments[0]==true) ? ArrayHash::from($this->annotations[$anchor]) : ArrayHash::from($this->annotations[$anchor][0]);
        } else {
            return null;
        }
    }
}
