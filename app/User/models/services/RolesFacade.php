<?php
namespace occ2\inventar\User\models;

use occ2\model\Model as BaseModel;
use Nette\Security\User;
use Contributte\Utils\DatetimeFactory;
use Contributte\EventDispatcher\EventDispatcher;
use occ2\inventar\User\models\repositories\Roles;
use occ2\inventar\User\models\events\RolesEvent;
use occ2\inventar\User\models\exceptions\RolesException;
use occ2\inventar\User\models\AuthorizatorFacade;
use Nette\Utils\ArrayHash;

/**
 * RolesFacade
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
final class RolesFacade extends BaseModel
{
    const USER_NOTIFY_ADD_ROLE="userNotifyAddRole",
          USER_NOTIFY_REMOVE_ROLE="userNotifyRemoveRole",
          EVENT_USER_ADD_ROLE="user.roles.add",
          EVENT_USER_REMOVE_ROLE="user.roles.remove";

    /**
     * @var \occ2\inventar\User\models\repositories\Roles
     */
    private $rolesRepository;

    /**
     * @var array
     */
    private $catalog;

    /**
     * @param DatetimeFactory $datetimeFactory
     * @param User $user
     * @param EventDispatcher $eventDispatcher
     * @param Roles $rolesRepository
     * @param array $config
     * @return void
     */
    public function __construct(DatetimeFactory $datetimeFactory,
                                User $user,
                                EventDispatcher $eventDispatcher,
                                Roles $rolesRepository,
                                AuthorizatorFacade $authorizator,
                                $config = []
    )
    {
        $this->rolesRepository = $rolesRepository;
        foreach ($authorizator->getRoles() as $role){
            $this->catalog[$role] = $role;
        }
        parent::__construct($datetimeFactory, $user, $eventDispatcher, $config);
        return;
    }

    /**
     * add role to user
     * @param int $userId
     * @param string $role
     * @return void
     * @throws RolesException
     * 
     * @aclResource users
     * @aclPrivilege write
     * @aclExceptionClass \occ2\inventar\User\models\exceptions\RolesException
     * @aclExceptionMessage "ERROR: You are not allowed to add users role"
     * @aclExceptionCode 1
     * @aclEventClass \occ2\inventar\User\models\events\RolesEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function addRole(int $userId,string $role)
    {
        $this->roleExists($role);
        $data=["userId"=>$userId,"role"=>$role];
        $this->_acl(__FUNCTION__,$data,null);
        if($this->isInRole($userId, $role)){
            throw new RolesException("ERROR: Users role is yet set", RolesException::ROLE_IS_IN_USE);
        } else {
            $this->rolesRepository->save(ArrayHash::from([
                Roles::USER=>$userId,
                Roles::ROLE=>$role
            ]));
        }
        $this->fireEvent(
            static::EVENT_USER_ADD_ROLE,
            new RolesEvent($data, static::EVENT_USER_ADD_ROLE)
        );
        return;
    }

    /**
     * remove users role
     * @param int $id
     * @return void
     *
     * @aclResource users
     * @aclPrivilege write
     * @aclExceptionClass \occ2\inventar\User\models\exceptions\RolesException
     * @aclExceptionMessage "ERROR: You are not allowed to delete users role"
     * @aclExceptionCode 2
     * @aclEventClass \occ2\inventar\User\models\events\RolesEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function removeRole(int $id)
    {
        $data=["roleId"=>$id];
        $role = $this->rolesRepository->find($id);
        $this->_acl(__FUNCTION__,$data,null);
        $this->rolesRepository->delete($id);
        $this->fireEvent(
            static::EVENT_USER_ADD_ROLE,
            new RolesEvent([
                Roles::ROLE=>$role->{Roles::ROLE},
                Roles::USER=>$role->{Roles::USER}
                ],
            static::EVENT_USER_ADD_ROLE)
        );
        return;
    }

    /**
     * get roles
     *
     * @param int $userId
     * @return Selection
     *
     * @aclResource users
     * @aclExceptionClass \occ2\inventar\User\models\exceptions\RolesException
     * @aclExceptionMessage "ERROR: You are not allowed to get users role"
     * @aclExceptionCode 3
     * @aclEventClass \occ2\inventar\User\models\events\RolesEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function getRoles(int $userId)
    {
        $this->_acl(__FUNCTION__,$data=["userId"=>$userId],$userId);
        return $this->rolesRepository->findBy([
            Roles::USER=>$userId
        ]);
    }

    /**
     * list roles
     * @param int $userId
     * @return array
     * @aclResource users
     * @aclExceptionClass \occ2\inventar\User\models\exceptions\RolesExceps"
     * @aclExceptionCode 4
     * @aclEventClass \occ2\inventar\User\models\events\RolesEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function listRoles(int $userId=null) : array
    {
        $this->_acl(__FUNCTION__,$data=["userId"=>$userId],$userId);
        if($userId==null){
            return $this->catalog;
        } else {
            $userRoles = $this->getRoles($userId)->fetchPairs(Roles::ROLE,Roles::ROLE);
            $result = $this->catalog;
            foreach ($userRoles as $role){
                unset($result[$role]);
            }
            return $result;
        }
    }

    /**
     * get one role
     * @aclResource users
     * @aclExceptionClass \occ2\inventar\User\models\exceptions\RolesException
     * @aclExceptionMessage "ERROR: You are not allowed to get role"
     * @aclExceptionCode 5
     * @aclEventClass \occ2\inventar\User\models\events\RolesEvent
     * @aclEventAnchor "user.manager.error"
     * @param int $id
     * @return ActiveRow
     */
    public function get(int $id)
    {
        $this->_acl(__FUNCTION__,$data=["userId"=>$userId],$userId);
        return $this->rolesRepository->find($id);
    }

    private function isInRole(int $userId,string $role)
    {
        $r = $this->rolesRepository->findOneBy([
            Roles::USER=>$userId,
            Roles::ROLE=>$role
        ]);
        return !$r ? false : true;
    }

    private function roleExists(string $role)
    {
        if(!in_array($role, $this->catalog)){
            throw new RolesException("ERROR: Invalid role", RolesException::INVALID_ROLE);
        }
        return true;
    }
}