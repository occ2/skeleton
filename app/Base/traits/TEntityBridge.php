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

namespace app\Base\traits;

use app\Base\exceptions\EntityException;
use Nette\Utils\Strings;
use Nette\Utils\ArrayHash;
use Nette\Reflection\ClassType;

/**
 * TEntityBridge
 *
 * trait that extend Doctrine2 entity to be convertable from/to array or ArrayHash
 * extend entity by universal getter and setter
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
trait TEntityBridge
{
    /**
     * create entity from array or ArrayHash
     * @param array | ArrayHash $arr
     * @param boolean $ignoreUndefinedSetter
     * @return \static
     * @throws EntityException
     */
    public static function from($arr,bool $ignoreUndefinedSetter=false)
    {
        $obj = new static;
        foreach ($arr as $key=>$value){
            $this->set($key, $value, $ignoreUndefinedSetter);
        }
        return $obj;
    }

    /**
     * fill entity by values
     * @param ArrayHash | array $arr
     * @param bool $ignoreUndefinedSetter
     * @return $this
     * @throws EntityException
     */
    public function fill($arr,bool $ignoreUndefinedSetter=false)
    {
        foreach ($arr as $key=>$value){
            $this->set($key, $value, $ignoreUndefinedSetter);
        }
        return $this;
    }

    /**
     * convert entity to array
     * @param bool $ignoreUndefinedGetter
     * @return array
     * @throws EntityException
     */
    public function toArray(bool $ignoreUndefinedGetter=true): array
    {
        $res = [];
        $ref = new ClassType(static::class);
        foreach ($ref->getProperties() as $property) {
            $res[$property->name] = $this->get($property->name,$ignoreUndefinedGetter);
        }
        return $res;
    }

    /**
     * convert entity to ArrayHash
     * @param bool $ignoreUndefinedGetter ignore non-existent property?
     * @return ArrayHash
     * @throws EntityException
     */
    public function toArrayHash(bool $ignoreUndefinedGetter=true): ArrayHash
    {
        $res = new ArrayHash();
        $ref = new ClassType(static::class);
        foreach ($ref->getProperties() as $property) {
            $res->{$property->name} = $this->get($property->name,$ignoreUndefinedGetter);
        }
        return $res;
    }

    /**
     * universal getter
     * @param string $name name of property
     * @param type $ignoreUndefined ignore non-existent property?
     * @return mixed | null
     * @throws EntityException
     */
    public function get(string $name,bool $ignoreUndefined=false)
    {
        $method = "get" . Strings::firstUpper($name);
        if(!method_exists($this, $method)){
            if(!$ignoreUndefined){
                throw new EntityException("base.error.entity.getter",EntityException::UNDEFINED_GETTER);
            } else {
                return null;
            }
        } else{
            return $this->$method();
        }
    }

    /**
     * universal setter
     * @param string $name name of property
     * @param mixed $value value of property
     * @param bool $ignoreIndefined ignore non-existent property?
     * @return $this
     * @throws EntityException
     */
    public function set(string $name,$value,bool $ignoreIndefined=false)
    {
        $method = "set" . Strings::firstUpper($name);
        if(!method_exists($this, $method)){
            if(!$ignoreIndefined){
                throw new EntityException("base.error.entity.setter",EntityException::UNDEFINED_SETTER);
            }
        } else{
            $this->$method($value);
        }
        return $this;
    }
}