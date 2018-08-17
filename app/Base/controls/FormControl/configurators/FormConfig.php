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
     * @var FormControl
     */
    protected $parent;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @param FormControl $parent
     * @return void
     */
    public function __construct(FormControl $parent)
    {
        $classType = ClassType::from($parent);
        $this->cache = $parent->_cacheFactory->create(static::CACHE_PREFIX);
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

    /**
     * annotation getter
     * @param string $name
     * @return array | null
     */
    public function __get($name):?array
    {
        $anchor = Strings::firstLower($name);
        if(array_key_exists($anchor, $this->annotations)){
            return $this->annotations[$anchor];
        } else {
            return null;
        }
    }
}
