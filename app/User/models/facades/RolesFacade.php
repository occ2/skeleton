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

namespace app\User\models\facades;

use app\Base\models\facades\BaseFacade;
use app\User\models\entities\Role as RoleEntity;
use app\User\models\entities\User as UserEntity;
use app\User\models\exceptions\RolesException;
use app\User\models\exceptions\ProfileException;
use app\User\events\data\RolesEvent;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\DI\Config\Helpers;

/**
 * RolesFacade
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class RolesFacade extends BaseFacade
{
    const ENTITY_CLASS=RoleEntity::class,
          EVENT_LOAD="User.RolesEvents.onLoad",
          EVENT_FIND="User.RolesEvents.onFind",
          EVENT_ADD="User.RolesEvents.onAdd",
          EVENT_REMOVE="User.RolesEvents.onRemove",
          EVENT_SET_DEFAULT="User.RolesEvents.onSetDefault";

    /**
     * @var array
     */
    protected $defaultRoles=[];

    /**
     * set default roles
     * @param array | string $defaultRoles
     * @return void
     */
    public function setDefaultRoles($defaultRoles)
    {
        $this->defaultRoles = (array) Helpers::merge($defaultRoles, $this->defaultRoles);
        return;
    }

    /**
     * load roles
     * @param int $userId
     * @return Collection | null
     */
    public function load(int $userId): ?Collection
    {
        // find user
        $user = $this->em->find(UserEntity::class,$userId);
        $this->testFound($user, ProfileException::class);

        // get user roles
        $roles = $user!=null ? $user->getRoles() : null;

        // fire event
        $this->on(
            static::EVENT_LOAD,
            new RolesEvent(
                [
                    RolesEvent::COLLECTION=>$roles
                ],
                static::EVENT_LOAD
            )
        );

        return $roles;
    }

    /**
     * find one role
     * @param int $id
     * @return RoleEntity | null
     */
    public function find(int $id): ?RoleEntity
    {
        // find entity
        $role = $this->em->find(RoleEntity::class, $id);
        $this->testFound($role, RolesException::class);

        // fire event
        $this->on(
            static::EVENT_FIND,
            new RolesEvent(
                [
                    RolesEvent::ENTITY=>$role
                ],
                static::EVENT_FIND
            )
        );
        return $role;
    }

    /**
     * add new user role
     * @param string $role
     * @param int $userId
     * @return void
     */
    public function add(string $role,int $userId)
    {
        // find user
        $user = $this->em->find(UserEntity::class, $userId);
        $this->testFound($user, ProfileException::class);
        $this->testUnique($user, $role);
        $this->testRole($role);

        // create new role entity
        if($user!=null){
            $entity = new RoleEntity;
            $entity->setUser($user);
            $entity->setRole($role);
            $this->em->persist($entity);
            $this->em->flush();

            // fire event
            $this->on(
                static::EVENT_ADD,
                new RolesEvent(
                    [
                        RolesEvent::ENTITY=>$role
                    ],
                    static::EVENT_ADD
                )
            );
        }
        return;
    }

    /**
     * remove entity
     * @param int $id
     * @return void
     */
    public function remove(int $id)
    {
        // find entity to delete
        $role = $this->find($id);
        $this->testFound($role, RolesException::class);

        if($role!=null){
            // delete entity
            $this->em->remove($role);

            // save to DB
            $this->em->flush();

            // fire event
            $this->on(
                static::EVENT_REMOVE,
                new RolesEvent(
                    [
                        RolesEvent::ENTITY=>$role
                    ],
                    static::EVENT_REMOVE
                )
            );
        }
        return;
    }

    /**
     * set default user roles
     * @param UserEntity $user
     * @return void
     */
    public function setDefault(UserEntity $user)
    {
        $arr = [];
        // iterate defautl roles
        foreach ($this->defaultRoles as $role){
            // crete new role 
            $entity = new RoleEntity;

            // set user and value
            $entity->setUser($user)
                   ->setRole($role);

            $this->em->persist($entity);
            $arr[] = $entity;
        }

        // save to DB
        $this->em->flush();

        // fire event
        $this->on(
            static::EVENT_SET_DEFAULT,
            new RolesEvent(
                [
                    RolesEvent::COLLECTION=>new ArrayCollection($arr)
                ],
                static::EVENT_SET_DEFAULT
            )
        );
        return;
    }

    /**
     * test if role of user is unique
     * @param UserEntity | null $user
     * @param string $role
     * @return void
     * @throws RolesException
     */
    private function testUnique(?UserEntity $user,string $role){
        if($user!=null){
            // try to find role
            $entity = $this->em
                           ->getRepository(RoleEntity::class)
                           ->findOneBy([
                                RoleEntity::USER=>$user,
                                RoleEntity::ROLE=>$role
                           ]);
            // if find throw exception
            if($entity!=null){
                throw new RolesException(RolesException::MESSAGE_ROLE_IN_USE, RolesException::ROLE_IS_IN_USE);
            }
        }
        return;
    }

    /**
     * test if role is in role catalogue
     * @param string $role
     * @return void
     * @throws RolesException
     */
    private function testRole(string $role){
        if(!in_array($role, $this->config)){
            throw new RolesException(RolesException::MESSAGE_INVALID_ROLE, RolesException::INVALID_ROLE);
        }
        return;
    }
}