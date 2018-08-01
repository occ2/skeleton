<?php
namespace app\Base\traits;

use app\Base\exceptions\EntityException;
use Nette\Utils\Strings;
use Nette\Utils\ArrayHash;
use Nette\Reflection\ClassType;

/**
 * TEntityBridge
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
trait TEntityBridge
{
    public static function from($arr,$ignoreUndefinedSetter=false)
    {
        $obj = new static;
        foreach ($arr as $key=>$value){
            $setter="set" . Strings::firstUpper($key);
            if(!method_exists($obj, $setter)){
                if($ignoreUndefinedSetter==false){
                    throw new EntityException("base.error.entity.setter",EntityException::UNDEFINED_SETTER);
                }
            } else {
                $obj->$setter($value);
            }
        }
        return $obj;
    }

    public function toArray($ignoreUndefinedGetter=true)
    {
        $res = [];
        $ref = new ClassType(static::class);
        foreach ($ref->getProperties() as $property) {
            $method = "get" . Strings::firstUpper($property->name);
            if(!method_exists($this, $method)){
                if($ignoreUndefinedGetter==false){
                    throw new EntityException("base.error.entity.getter",EntityException::UNDEFINED_GETTER);
                }
            } else{
                $res[$property->name] = $this->$method();
            }
        }
        return $res;
    }

    public function toArrayHash($ignoreUndefinedGetter=true)
    {
        $res = new ArrayHash();
        $ref = new ClassType(static::class);
        foreach ($ref->getProperties() as $property) {
            $method = "get" . Strings::firstUpper($property->name);
            if(!method_exists($this, $method)){
                if($ignoreUndefinedGetter==false){
                    throw new EntityException("base.error.entity.getter",EntityException::UNDEFINED_GETTER);
                }
            } else {
                $res->{$property->name} = $this->$method();
            }
        }
        return $res;
    }
}