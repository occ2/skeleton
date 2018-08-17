<?php
/*
 * The MIT License
 *
 * Copyright 2018 Milan Onderka <milan_onderka@occ2.cz>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

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