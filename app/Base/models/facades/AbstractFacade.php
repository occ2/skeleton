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

namespace app\Base\models\facades;

use app\Base\models\entities\IEntity;
use app\Base\events\Event;
use app\Base\exceptions\AbstractException;
use Doctrine\ORM\EntityManager;
use Contributte\EventDispatcher\EventDispatcher;
use Contributte\Utils\DatetimeFactory;
use Nette\DI\Config\Helpers;
use Nette\Security\User;
use Nette\Utils\Strings;
use Contributte\Cache\ICacheFactory;
use Nette\Reflection\ClassType;

/**
 * AbstractFacade
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
abstract class AbstractFacade
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var EventDispatcher
     */
    protected $ed;

    /**
     * @var User | null
     */
    protected $user;

    /**
     * @var DatetimeFactory
     */
    protected $datetimeFactory;

    /**
     * @var ICacheFactory
     */
    protected $cachingFactory;
    
    /**
     * @var array
     */
    protected $config=[];

    /**
     * @param DatetimeFactory $datetimeFactory
     * @param ICacheFactory $cacheFactory
     * @param EntityManager $em
     * @param EventDispatcher $ed
     * @param User $user
     * @param array $config
     * @return void
     */
    public function __construct(
        DatetimeFactory $datetimeFactory,
        ICacheFactory $cacheFactory,
        EntityManager $em,
        EventDispatcher $ed,
        User $user=null,
        array $config=[])
    {
        $this->em = $em;
        $this->ed = $ed;
        $this->user = $user;
        $this->datetimeFactory = $datetimeFactory;
        $this->cachingFactory = $cacheFactory;
        $this->config = (array) Helpers::merge($config, $this->config);
        return;
    }

    /**
     * nette security user setter
     * @param User $user
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * fire event
     * @param string $anchor
     * @param Event $event
     * @return mixed
     */
    public function on(string $anchor, Event $event=null)
    {
        return $this->ed->dispatch($anchor, $event);
    }

    /**
     * test if entity found
     * @param IEntity $entity
     * @param string $exceptionClass
     * @return IEntity
     * @throws AbstractException
     */
    protected function testFound($entity,string $exceptionClass=null){
        if($entity==null && $exceptionClass!=null){
            $classType = ClassType::from($exceptionClass);
            $code = $classType->getConstant("NOT_FOUND")==false ? 404 : $classType->getConstant("NOT_FOUND");
            $message = $classType->getConstant("MESSAGE_NOT_FOUND")==false ? 404 : $classType->getConstant("MESSAGE_NOT_FOUND");
            throw new $exceptionClass($message,$code);
        }
        return $entity;
    }

    /**
     * exclude $exclude from $data array
     * @param array $data
     * @param array $exclude
     * @return array
     */
    protected function exclude(array $data=[],array $exclude=[]): array
    {
        foreach ($exclude as $value){
            if(array_key_exists($value, $data)){
                unset($data[$value]);
            }
        }
    }

    /**
     * modify entity from data with excluded cols
     * @param IEntity $entity
     * @param array $data
     * @param array $exclude
     * @return IEntity
     */
    protected function modify(IEntity $entity,array $data,array $exclude=[]): IEntity
    {
        foreach ($data as $key=>$value){
            $method = "set" . Strings::firstUpper($key);
            if(method_exists($entity, $method) && !in_array($method, $exclude)){
                $entity->$method($value);
            }
        }
        return $entity;
    }
}