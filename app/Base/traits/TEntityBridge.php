<?php
namespace app\Base\traits;

use app\Base\exceptions\EntityException;
use Nette\Utils\Strings;
use Nette\Utils\ArrayHash;
use Nette\Reflection\ClassType;

/**
 * TEntityBridge
 *
 * trait that extend Doctrine2 entity to be convertable from/to array or ArrayHash
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

    /**
     * convdert entity to ArrayHash
     * @param bool $ignoreUndefinedGetter
     * @return ArrayHash
     * @throws EntityException
     */
    public function toArrayHash(bool $ignoreUndefinedGetter=true): ArrayHash
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