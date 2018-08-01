<?php
namespace occ2\inventar\User\models\facades;

use Nettrine\ORM\EntityManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Contributte\EventDispatcher\EventDispatcher;
use Nette\Security\User as NUser;
use Nette\DI\Config\Helpers;
use Nette\Utils\ArrayHash;
use Nette\Utils\Random;
use occ2\model\TFacadePermissions;
use occ2\inventar\User\models\exceptions\ProfilesException;
use occ2\inventar\User\models\entities\User as UserEntity;
use occ2\inventar\User\models\entities\Role as RoleEntity;
use occ2\inventar\User\models\entities\Config as ConfigEntity;
use occ2\inventar\User\controls\forms\RegisterForm;

/**
 * Profile
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
final class Profiles
{
    use TFacadePermissions;

    const ADMIN_ROLE="administrator",
          ACL_RESOURCE_USER="users",

          EVENT_REGISTER="user.manager.register",
          EVENT_CHANGE_PASSWORD="user.manager.changePassword",
          EVENT_SAVE_SETTINGS="user.manager.saveSettings",
          EVENT_ADD_USER="user.manager.addUser",
          EVENT_EDIT_USER="user.manager.editUser",
          EVENT_CHANGE_USER_STATUS="user.manager.changeStatus",
          EVENT_DELETE_USER="user.manager.deleteUser",
          EVENT_RESET_PASSWORD="user.manager.resetPassword",
          EVENT_UNAUTHORIZED_USERS_LISTING="user.manager.error",
          EVENT_UNAUTHORIZED_USER_STATUS_CHANGE="user.manager.error",
          EVENT_UNAUTHORIZED_USER_LOAD="user.manager.error",
          EVENT_UNAUTHORIZED_HISTORY_LOAD="user.manager.error",
          EVENT_UNAUTHORIZED_CONFIG_LOAD="user.manager.error", //
          EVENT_UNAUTHORIZED_USER_ADD="user.manager.error",
          EVENT_UNAUTHORIZED_USER_EDIT="user.manager.error",
          EVENT_UNAUTHORIZED_USER_DELETE="user.manager.error",
          EVENT_UNAUTHORIZED_PASSWORD_RESET="user.manager.error",
        
          USER_CONFIG_NOTIFY_EDIT="userNotifyAdminChange",
          USER_CONFIG_NOTIFY_ADMIN_PASSWORD="userNotifyAdminChangePassword",
          USER_CONFIG_NOTIFY_PASSWORD="userNotifyChangePassword",
          USER_CONFIG_NOTIFY_CHANGE_STATUS="userNotifyChangeStatus"
        ;

    const STATUSES = [
        "cz"=>[
            0=>"Neaktivní",
            1=>"Aktivní"
        ],
    ];

    /**
     * @var \Nettrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var array
     */
    private $config=[
        "defaultLang"=>"cz",
        "defaultStatus"=>true,
        "secretLength"=>8,
        "passwordExpiration"=>"+90 Days",
        "defaultRoles"=>[
            "authenticated"
        ]
    ];

    /**
     * @var array
     */
    private $defaultUserConfig=[];

    /**
     * @param EntityManager $em
     * @param EventDispatcher $ed
     * @param NUser $user
     * @param array $config
     * @param array $defaultUserConfig
     * @return void
     */
    public function __construct(
        EntityManager $em,
        EventDispatcher $ed,
        NUser    $user,
        array $config=[],
        array $defaultUserConfig=[]
    )
    {
        $this->em = $em;
        $this->ed = $ed;
        $this->user = $user;
        $this->defaultUserConfig = $defaultUserConfig;
        $this->config = Helpers::merge($config, $this->config);
        return;
    }

    /**
     * register new user
     * @param ArrayHash $values
     * @return void
     * @throws UsersException
     * @throws ProfilesException
     */
    public function registerUser(ArrayHash $values)
    {
        if ($values->{RegisterForm::PASSWORD}!==$values->{RegisterForm::REPEATED_PASSWORD}) {
            throw new ProfilesException("user.error.profiles.repeatedPassword", ProfilesException::PASSWORDS_NOT_SAME);
        }

        try {
            $user = UserEntity::from($values, true);
            $user->setSecret(Random::generate($this->config["secretLength"]))
                 ->setPasswordExpiration($this->config["passwordExpiration"])
                 ->setAttempts(0)
                 ->setLang($this->config["defaultLang"])
                 ->setStatus($this->config["defaultStatus"]);
            $this->em->persist($user);
            $this->setDefaultRoles($user);
            $this->setDefaultSettings($user);
            $this->em->flush();
        } catch (\Exception $exc) {
            bdump ($exc->getSQLState());
        }
    }

    /**
     * change users expired password
     * @param ArrayHash $values
     * @return void
     */
    public function changeExpiredPassword(ArrayHash $values)
    {

    }

    /**
     * save users settings
     * @param ArrayHash $values
     * @return int
     * @throws UsersException
     *
     * @currentUser true
     * @aclExceptionClass \occ2\inventar\User\models\exceptions\UsersException
     * @aclExceptionMessage "ERROR: users id must be same as logged users id"
     * @aclExceptionCode 2202
     * @aclEventClass \occ2\inventar\User\models\events\SettingsEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function saveSettings(ArrayHash $values)
    {

    }

    /**
     * change users password
     * @param ArrayHash $values
     * @return void
     * @throws UsersException
     *
     * @currentUser true
     * @aclExceptionClass \occ2\inventar\User\models\exceptions\UsersException
     * @aclExceptionMessage "ERROR: users id must be same as logged users id"
     * @aclExceptionCode 2202
     * @aclEventClass \occ2\inventar\User\models\events\PasswordEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function changePassword(ArrayHash $values)
    {
        
    }

    /**
     * get users history filtered/unfiltered repository
     * @param int $usersId
     * @return Selection
     *
     * @currentUser true
     * @aclResource users
     * @aclExceptionClass \occ2\inventar\User\models\exceptions\UsersException
     * @aclExceptionMessage "ERROR: You are not allowed to view users history"
     * @aclExceptionCode 2206
     * @aclEventClass \occ2\inventar\User\models\events\UsersEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function getHistory(int $usersId=null)
    {
        
    }

    /**
     * change users status
     * @param int $id
     * @param int $status
     * @return int
     * @throws UsersException
     *
     * @aclResource users
     * @aclPrivilege write
     * @aclExceptionClass \occ2\inventar\User\models\exceptions\UsersException
     * @aclExceptionMessage "ERROR: You are not authorized to change users status"
     * @aclExceptionCode 2204
     * @aclEventClass \occ2\inventar\User\models\events\UsersEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function userChangeStatus(int $id, int $status)
    {
        
    }

    /**
     * check permissions and load all users
     * @return mixed
     * @throws UsersException
     *
     * @aclResource users
     * @aclExceptionClass \occ2\inventar\User\models\exceptions\UsersException
     * @aclExceptionMessage "ERROR: You have no permission to list users."
     * @aclExceptionCode 2203
     * @aclEventClass \occ2\inventar\User\models\events\UsersEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function allUsers()
    {

    }

    /**
     * get user by ID
     * @param integer $id
     * @return \Nette\Database\ActiveRow
     * @throws AuthenticationException
     *
     * @currentUser true
     * @aclResource users
     * @aclExceptionClass \occ2\inventar\User\models\exceptions\UsersException
     * @aclExceptionMessage "ERROR: You are not allowed to load users data"
     * @aclExceptionCode 2205
     * @aclEventClass \occ2\inventar\User\models\events\UsersEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function getUser($id,$toArrayHash=false)
    {
        
    }

    /**
     * add new user
     * @param ArrayHash $values
     * @return ArrayHash
     * @throws UsersException
     *
     * @aclResource users
     * @aclPrivilege add
     * @aclExceptionClass \occ2\inventar\User\models\exceptions\UsersException
     * @aclExceptionMessage "ERROR: Unauthorized user add"
     * @aclExceptionCode 2207
     * @aclEventClass \occ2\inventar\User\models\events\UsersEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function addUser(ArrayHash $values)
    {
        
    }

    /**
     * edit user
     * @param ArrayHash $values
     * @return ArrayHash
     * @throws UsersException
     *
     * @aclResource users
     * @aclPrivilege write
     * @aclExceptionClass \occ2\inventar\User\models\exceptions\UsersException
     * @aclExceptionMessage "ERROR: Unauthorized user edit"
     * @aclExceptionCode 2208
     * @aclEventClass \occ2\inventar\User\models\events\UsersEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function editUser(ArrayHash $values)
    {
        
    }

    /**
     * delete user
     * @param int $id
     * @return int
     * @throws UsersException
     *
     * @aclResource users
     * @aclPrivilege delete
     * @aclExceptionClass \occ2\inventar\User\models\exceptions\UsersException
     * @aclExceptionMessage "ERROR: Unauthorized user delete"
     * @aclExceptionCode 2215
     * @aclEventClass \occ2\inventar\User\models\events\UsersEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function deleteUser(int $id)
    {
        
    }

    /**
     * reset users password
     * @param int $id
     * @return ArrayHash
     * @throws UsersException
     *
     * @aclResource users
     * @aclPrivilege write
     * @aclExceptionClass \occ2\inventar\User\models\exceptions\UsersException
     * @aclExceptionMessage "ERROR: Unauthorized password reset"
     * @aclExceptionCode 2209
     * @aclEventClass \occ2\inventar\User\models\events\UsersEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function resetPassword(int $id)
    {
        
    }

    /**
     * get list of system admins
     * @return ArrayHash[]
     */
    public function getAdmins()
    {
        
    }

    // PRIVATE AREA

    private function setDefaultRoles(UserEntity $user)
    {
        foreach ($this->config["defaultRoles"] as $role) {
            $entity = new RoleEntity;
            $entity->setUser($user)
                   ->setRole($role);
            $this->em->persist($entity);
            $this->em->flush();
        }
        return;
    }

    private function setDefaultSettings(UserEntity $user)
    {
        foreach ($this->defaultUserConfig as $config) {
            $entity = new ConfigEntity;
            $entity->setUser($user)
                   ->setKey($config["key"])
                   ->setValue($config["value"])
                   ->setComment($config["comment"])
                   ->setType($config["type"]);
            $this->em->persist($entity);
            $this->em->flush();
        }
        return;
    }
}