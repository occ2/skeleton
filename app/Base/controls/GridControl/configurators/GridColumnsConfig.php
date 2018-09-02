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
use app\Base\controls\Control\IConfigurator;
use Nette\Reflection\Property;
use Nette\Caching\Cache;
use Nette\Reflection\ClassType;

/**
 * FormItemConfig
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class GridColumnsConfig implements IConfigurator
{
    const CACHE_PREFIX="grid";

    /**
     * @var array
     */
    protected $annotations;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @param Property $property
     * @param GridControl $parent
     * @return void
     */
    public function __construct(Property $property, GridControl $parent)
    {
        $this->name = $property->getName();
        $classType = ClassType::from($parent);
        $this->cache = $parent->getCacheFactory()->create(self::CACHE_PREFIX);
        $this->annotations = $this->cache->load($classType->getName() . "." . $this->name);
        if($this->annotations===null){
            $this->annotations = $property->getAnnotations();
            $this->cache->save($classType->getName() . "." . $this->name, $this->annotations,[
                Cache::FILES => $classType->getFileName()
            ]);
        }
        
        return;
    }

    /**
     * get property annotation
     * @param string $name
     * @param bool $multiple
     * @return mixed | null
     */
    public function get(string $name, bool $multiple = false)
    {
        if($name=="name"){
            return $this->name;
        } elseif (array_key_exists($name, $this->annotations)) {
            return $multiple==true ? $this->annotations[$name] : $this->annotations[$name][0];
        } else {
            return null;
        }
    }
}
