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

use app\User\models\facades\BaseFacade;
use app\User\models\facades\TUserDefaults;
use app\User\models\entities\User as UserEntity;
use app\User\models\entities\Role as RoleEntity;
use app\User\models\facades\AuthorizationFacade;
use app\User\models\exceptions\AdminException;
use app\User\events\data\AdminEvent;
use Doctrine\ORM\QueryBuilder;
use Nette\Utils\Random;
use Nette\Utils\ArrayList;

/**
 * AdminFacade
 * class for user administration
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 * @todo ADD ACL Conditions !!!
 */
final class AdminFacade extends BaseFacade
{
    use TUserDefaults;

    const EVENT_SAVE="User.AdminFacade.onSave",
          EVENT_LOAD="User.AdminFacade.onLoad",
          EVENT_GET="User.AdminFacade.onGet",
          EVENT_FIND="User.AdminFacade.onFind",
          EVENT_ADD="User.AdminFacade.onAdd",
          EVENT_REMOVE="User.AdminFacade.onRemove",
          EVENT_CHANGE_STATUS="User.AdminFacade.onStatusChange";

    /**
     * @var array
     */
    protected $config=[
        "randomPasswordLength"=>8,
        "randomSecretLength"=>8,
        "passwordExpiration"=>"+90 Days",
        "defaultStatus"=>1,
        "defaultLang"=>"cz",
        "defaultQuestion"=>"default",
        "defaultAnswer"=>"default"
    ];

    /**
     * load users
     * @return QueryBuilder
     */
    public function load(): QueryBuilder
    {
        // create query builder for datagrid
        $query = $this->em->createQueryBuilder()
                          ->select("u")
                          ->from(UserEntity::class,"u");

        // fire event
        $this->on(
            static::EVENT_LOAD,
            new AdminEvent(
                [
                    AdminEvent::QUERY_BUILDER=>$query
                ],
                static::EVENT_LOAD
            )
        );
        return $query;
    }

    public function get(int $userId,$toHash=false)
    {
        // get user entity
        $user = $this->em->find(UserEntity::class, $userId);
        // fire event
                // fire event
        $this->on(
            static::EVENT_GET,
            new AdminEvent(
                [
                    AdminEvent::ENTITY=>$user
                ],
                static::EVENT_GET
            )
        );
        // return entity
        if($toHash==false){
            return $user;
        } else {
            return $user->toArrayHash();
        }
    }

    /**
     * save user data
     * @param array $data
     * @param array $exclude
     * @return void
     */
    public function save(array $data,array $exclude=[])
    {
        // find user
        $u = $this->em->find(UserEntity::class, $data[UserEntity::ID]);
        $this->testFound($u, AdminException::class);

        if($u!=null){
            // modify entity data
            $user = $this->modify($u, $data, $exclude);

            // save to DB
            $this->em->flush();

            // fire event
            $this->on(
                static::EVENT_SAVE,
                new AdminEvent(
                    [
                    AdminEvent::ENTITY=>$user
                    ],
                    static::EVENT_SAVE
                )
            );
        }

        return;
    }

    /**
     * add new user
     * @param array $data
     * @param array $exclude
     * @return void
     * @throws AdminException
     */
    public function add(array $data,array $exclude=[])
    {
        unset($data[UserEntity::ID]);
        // test user has unique username
        $_user = $this->em
                      ->getRepository(UserEntity::class)
                      ->findBy(
                              [
                                  UserEntity::USERNAME=>$data[UserEntity::USERNAME]
                              ]
                        );
        if($_user!=null){
            throw new AdminException(AdminException::MESSAGE_NOT_UNIQUE,AdminException::USERNAME_NOT_UNIQUE);
        }

        // generate password
        $password = Random::generate($this->config["randomPasswordLength"]);

        // create new entity and fill with data
        $u = new UserEntity;
        $u->fill($this->exclude($data, $exclude));
        $u->setPassword($password, false)
          ->setCQuestion($this->config["defaultQuestion"])
          ->setCAnswer($this->config["defaultAnswer"]);
        $secret = $this->setDefaults($u);
        $this->em->persist($u);

        // save into DB
        $this->em->flush();

        // fire event
        $this->on(
            static::EVENT_ADD,
            new AdminEvent(
                [
                    AdminEvent::ENTITY=>$u,
                    AdminEvent::PASSWORD=>$password,
                    AdminEvent::SECRET=>$secret
                ],
                static::EVENT_ADD
            )
        );
        return;
    }

    /**
     * delete user
     * @param int $id
     * @return void
     */
    public function remove(int $id)
    {
        // find entity
        $user = $this->em->find(UserEntity::class, $id);
        $this->testFound($user, AdminException::class);
        if($user!=null){
            // remove it from repository
            $this->em->remove($user);

            // save into DB
            $this->em->flush();

            // fire event
            $this->on(
                static::EVENT_REMOVE,
                new AdminEvent(
                    [
                        AdminEvent::ENTITY=>$user
                    ],
                    static::EVENT_REMOVE
                )
            );
        }

        return;
    }

    /**
     * change user status
     * @param int $id
     * @param bool $status
     * @return void
     */
    public function changeStatus(int $id,bool $status)
    {
        // find user entity
        $user = $this->em->find(UserEntity::class, $id);
        $this->testFound($user, AdminException::class);

        if($user!=null){
            // set new status
            $user->setStatus($status);

            // save into DB
            $this->em->flush();

            // fire event
            $this->on(
                static::EVENT_CHANGE_STATUS,
                new AdminEvent(
                    [
                        AdminEvent::ENTITY=>$user
                    ],
                    static::EVENT_CHANGE_STATUS
                )
            );
        }
        return;
    }

    /**
     * get all admins
     * @return ArrayList
     */
    public function getAdmins(): ArrayList
    {
        $users = new ArrayList;

        // find all admin role entities
        $roles = $this->em
                      ->getRepository(RoleEntity::class)
                      ->findBy([RoleEntity::ROLE=> AuthorizationFacade::ROLE_ADMINISTRATOR]);

        // iterate, extract users and put into ArrayList
        foreach($roles as $role){
            $users[]=$role->getUser();
        }
        
        return $users;
    }
}