<?php
namespace app\Base\controls\DialogControl;

use app\Base\controls\Control\Control;
use Contributte\EventDispatcher\EventDispatcher;
use Contributte\Cache\ICacheFactory;
use Nette\Caching\Cache;
use Kdyby\Translation\ITranslator;
use Nette\Reflection\ClassType;
use Nette\Reflection\Property;
use Nette\Application\UI\ITemplate;
use Nette\Utils\Strings;

/**
 * DialogControl
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
abstract class DialogControl extends Control
{
    const CACHE_PREFIX = "dialogs";
    
    /**
     * @var string
     */
    public $_templatePath=__DIR__ . "/dialogControl.latte";

    /**
     * @var Cache
     */
    protected $_cache;

    protected $_annotationsConfig;

    public function __construct(EventDispatcher $eventDispatcher,
                                ICacheFactory $cacheFactory,
                                ITranslator $translator = null)
    {
        parent::__construct($eventDispatcher, $cacheFactory, $translator);
        $this->_cache = $this->_cacheFactory->create(static::CACHE_PREFIX);
        $this->getAnnotationsConfig();
    }

    public function render()
    {
        if($this->template instanceof ITemplate){
            $this->template->setFile($this->_templatePath);
            $this->template->render();
        }
        return;
    }

    protected function getAnnotationsConfig(){
        $classType= ClassType::from($this);
        foreach ($classType->getProperties() as $property){
            $this->getPropertyConfig($property,$classType);
        }
    }

    protected function getPropertyConfig(Property $property, ClassType $classType){
        $name = $property->getName();
        if (
           $name!="name" &&
           $name!="parent" &&
           $name!="presenter" &&
           $name!="params" &&
           $name!="snippetMode" &&
           $name!="linkCurrent" &&
           $name!="template" &&
           !Strings::startsWith($name, "_")
        ) {

            $this->_annotationsConfig[$name] = $this->_cache->load(
                $classType->getName() . "." . $name
            );
            if($this->_annotationsConfig[$name]===null){
                $this->_annotationsConfig[$name] = $property->getAnnotations();
                $this->_cache->save(
                    $classType->getName() . "." . $name,
                    $this->_annotationsConfig[$name],
                    [
                        Cache::FILES=>$classType->getFileName()
                    ]
                );
            }
        }
    }
}