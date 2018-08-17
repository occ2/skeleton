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

namespace app\Base\controls\GridControl\configurators;

use app\Base\controls\GridControl\GridControl;
use Nette\Reflection\ClassType;
use Nette\Utils\Strings;
use Nette\Utils\ArrayHash;
use Nette\Caching\Cache;

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

    const CACHE_PREFIX="grid";

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
     * @param GridControl $parent
     * @return void
     */
    public function __construct(GridControl $parent)
    {
        $this->parent = $parent;
        $classType = ClassType::from($parent);
        $this->cache = $parent->_cacheFactory->create(self::CACHE_PREFIX);
        $this->annotations = $this->cache->load($classType->getName());
        if($this->annotations===null){
            $this->annotations = $classType->getAnnotations();
            $this->cache->save($classType->getName(), $this->annotations,[
                Cache::FILES => $classType->getFileName()
            ]);
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
