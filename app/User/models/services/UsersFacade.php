<?php
namespace occ2\inventar\User\models;

use Nette\Utils\Random;
use Nette\Utils\ArrayHash;
use Nette\Security\Passwords;
use Nette\Security\User;
use Nette\Database\UniqueConstraintViolationException;
use Contributte\Utils\DateTimeFactory;
use Contributte\Utils\Strings;
use Contributte\EventDispatcher\EventDispatcher;
use occ2\model\Model as BaseModel;
use occ2\inventar\User\models\repositories\Users;
use occ2\inventar\User\models\repositories\Roles;
use occ2\inventar\User\models\repositories\UsersHistory;
use occ2\inventar\User\controls\forms\RegisterForm;
use occ2\inventar\User\controls\forms\ExpiredPassForm;
use occ2\inventar\User\controls\forms\ChangePassForm;
use occ2\inventar\User\models\exceptions\UsersException;
use occ2\inventar\User\models\exceptions\AuthenticationException;
use occ2\inventar\User\models\events\RegisterEvent;
use occ2\inventar\User\models\events\PasswordEvent;
use occ2\inventar\User\models\events\SettingsEvent;
use occ2\inventar\User\models\events\UsersEvent;
use occ2\inventar\User\models\events\AuthenticationEvent;
use occ2\inventar\User\models\AuthorizatorFacade;
use occ2\inventar\User\models\repositories\UsersConfig;
use occ2\model\ILogger;

/**
 * Userts facade
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
final class UsersFacade extends BaseModel
{
    const ADMIN_ROLE="administrator",
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
          ACL_RESOURCE_USER="users",
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
     * @var \occ2\inventar\User\models\repositories\Users
     */
    private $usersRepository;
    
    /**
     * @var \occ2\inventar\User\models\repositories\Roles
     */
    private $rolesRepository;
    
    /**
     * @var \occ2\inventar\User\models\repositories\UsersHistory
     */
    private $usersHistoryRepository;

    /**
     * @var \occ2\inventar\User\models\repositories\UsersConfig
     */
    private $usersConfigRepository;

    /**
     * @var array
     */
    private $defaultUserConfig=[];
    
    /**
     * @param DateTimeFactory $datetimeFactory
     * @param User $user
     * @param EventDispatcher $eventDispatcher
     * @param Users $usersRepository
     * @param Roles $rolesRepository
     * @param array $config
     * @return void
     */
    public function __construct(
        DateTimeFactory $datetimeFactory,
        User $user,
        EventDispatcher $eventDispatcher,
        Users $usersRepository,
        Roles $rolesRepository,
        UsersHistory $usersHistoryRepository,
        UsersConfig $usersConfigRepository,
        $config=[],
        $defaultUserConfig=[]
    ) {
        $this->usersRepository = $usersRepository;
        $this->rolesRepository = $rolesRepository;
        $this->usersHistoryRepository = $usersHistoryRepository;
        $this->usersConfigRepository = $usersConfigRepository;
        $this->defaultUserConfig = $defaultUserConfig;
        parent::__construct($datetimeFactory, $user, $eventDispatcher, $config);
        return;
    }
    
    /**
     * register new user
     * @param ArrayHash $values
     * @return void
     * @throws UsersException
     * @throws \Exception
     */
    public function registerUser(ArrayHash $values)
    {
        $datetime = $this->datetimeFactory->create();
        if ($values[RegisterForm::PASSWORD]!==$values[RegisterForm::REPEATED_PASSWORD]) {
            throw new UsersException("ERROR: Passwords must be same");
        }
        $values[Users::PASSWORD_HASH] = Passwords::hash($values[RegisterForm::PASSWORD]);
        $values[Users::CONTROL_ANSWER] = Passwords::hash(Strings::lower($values[RegisterForm::ANSWER]));
        $values[Users::PASSWORD_EXPIRATION] = $datetime->modify($this->config["passwordExpiration"]);
        $values[Users::SECRET] = Random::generate($this->config["secretLength"]);
        unset($values[RegisterForm::REPEATED_PASSWORD]);
        unset($values[RegisterForm::PASSWORD]);
        try {
            $user = $this->usersRepository->save($values);
            $this->setDefaultRoles($user->{Users::ID});
            $values->{Users::ID} = $user->{Users::ID};
            $this->setDefaultConfig($values->{Users::ID});
            $this->fireEvent(self::EVENT_REGISTER, new RegisterEvent($values, self::EVENT_REGISTER));
            return;
        } catch (\Exception $exc) {
            if ($exc instanceof UniqueConstraintViolationException) {
                throw new UsersException("ERROR: Username must be unique", UsersException::USERNAME_NOT_UNIQUE);
            } else {
                throw $exc;
            }
        }
    }
    
    /**
     * change users expired password
     * @param ArrayHash $values
     * @return void
     */
    public function changeExpiredPassword(ArrayHash $values)
    {
        $this->comparePasswords($values[ExpiredPassForm::NEW_PASSWORD], $values[ExpiredPassForm::REPEATED_PASSWORD]);
        $user = $this->loadUser($values[ExpiredPassForm::USERNAME]);
        $this->testPassword($user, $values[ExpiredPassForm::OLD_PASSWORD]);
        $this->savePassword($user, $values[ExpiredPassForm::NEW_PASSWORD]);
        $values[Users::ID] = $user->{Users::ID};
        $values[Users::EMAIL] = $user->{Users::EMAIL};
        $this->fireEvent(self::EVENT_CHANGE_PASSWORD, new PasswordEvent($values, self::EVENT_CHANGE_PASSWORD));
        return;
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
     * @aclExceptionCode 3
     * @aclEventClass \occ2\inventar\User\models\events\SettingsEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function saveSettings(ArrayHash $values)
    {
        $this->_acl(__FUNCTION__, (array) $values, $values->{Users::ID});
        if (empty($values[Users::CONTROL_ANSWER])) {
            unset($values[Users::CONTROL_ANSWER]);
        } else {
            $values[Users::CONTROL_ANSWER] = Passwords::hash($values[Users::CONTROL_ANSWER]);
        }
        $this->fireEvent(self::EVENT_SAVE_SETTINGS, new SettingsEvent((array) $values, self::EVENT_SAVE_SETTINGS));
        return $this->usersRepository->save($values);
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
     * @aclExceptionCode 3
     * @aclEventClass \occ2\inventar\User\models\events\PasswordEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function changePassword(ArrayHash $values)
    {
        $this->_acl(__FUNCTION__, (array) $values, $values->{Users::ID});
        $this->comparePasswords($values->{ChangePassForm::NEW_PASSWORD}, $values->{ChangePassForm::REPEATED_PASSWORD});
        $user = $this->getUser($values->{Users::ID});
        $this->testPassword($user, $values->{ChangePassForm::OLD_PASSWORD});
        $this->savePassword($user, $values->{ChangePassForm::NEW_PASSWORD});
        $values->{Users::EMAIL} = $user->{Users::EMAIL};
        $values->{Users::USERNAME} = $user->{Users::USERNAME};
        $this->fireEvent(self::EVENT_CHANGE_PASSWORD, new PasswordEvent($values, self::EVENT_CHANGE_PASSWORD));
        return;
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
     * @aclExceptionCode 7
     * @aclEventClass \occ2\inventar\User\models\events\UsersEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function getHistory(int $usersId=null)
    {
        $this->_acl(__FUNCTION__, ["user"=>$usersId], $usersId);
        if($usersId==null) {
            return $this->usersHistoryRepository->findAll();
        } else {
            return $this->usersHistoryRepository->findBy([UsersHistory::USER=>$usersId]);
        }
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
     * @aclExceptionCode 5
     * @aclEventClass \occ2\inventar\User\models\events\UsersEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function userChangeStatus(int $id, int $status)
    {
        $this->_acl(__FUNCTION__, ["user"=>$id], $id);
        $user = $this->usersRepository->find($id);
        $this->fireEvent(self::EVENT_CHANGE_USER_STATUS, new UsersEvent(ArrayHash::from($user->toArray()),self::EVENT_CHANGE_USER_STATUS));
        return $this->usersRepository->change($id, Users::STATUS, $status);
    }

    /**
     * check permissions and load all users
     * @return mixed
     * @throws UsersException
     *
     * @aclResource users
     * @aclExceptionClass \occ2\inventar\User\models\exceptions\UsersException
     * @aclExceptionMessage "ERROR: You have no permission to list users."
     * @aclExceptionCode 4
     * @aclEventClass \occ2\inventar\User\models\events\UsersEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function allUsers()
    {
        $this->_acl(__FUNCTION__);
        return $this->usersRepository->findAll();
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
     * @aclExceptionCode 6
     * @aclEventClass \occ2\inventar\User\models\events\UsersEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function getUser($id,$toArrayHash=false)
    {
        $this->_acl(__FUNCTION__, ["user"=>$id], $id);
        $user = $this->usersRepository->find($id);
        if (!$user) {
            $this->fireEvent(AuthenticatorFacade::EVENT_USER_NOT_FOUND, new AuthenticationEvent([null, ILogger::DANGER], AuthenticatorFacade::EVENT_USER_NOT_FOUND));
            throw new AuthenticationException("ERROR: Invalid user", AuthenticationException::IDENTITY_NOT_FOUND);
        }
        if($toArrayHash) {
            return ArrayHash::from($user->toArray());
        } else {
            return $user;
        }
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
     * @aclExceptionCode 8
     * @aclEventClass \occ2\inventar\User\models\events\UsersEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function addUser(ArrayHash $values)
    {
        $this->_acl(__FUNCTION__,(array) $values);
        try {
            $password = Random::generate($this->config["randomPasswordLength"]);
            $values->{Users::PASSWORD_HASH} = Passwords::hash($password);
            $values->{Users::PASSWORD_EXPIRATION} = $this->datetimeFactory->create();
            $values->{Users::STATUS} = 1;
            $values->{Users::SECRET} = Random::generate($this->config["secretLength"]);
            $_values = ArrayHash::from($this->usersRepository->save($values)->toArray());
            $_values->password = $password;
            $this->setDefaultConfig($_values->{Users::ID});
            $this->setDefaultRoles($_values->{Users::ID});
            $this->fireEvent(static::EVENT_ADD_USER, new UsersEvent($_values, static::EVENT_ADD_USER));
            return $_values;
        } catch (UniqueConstraintViolationException $exc) {
            throw new UsersException("ERROR: Username must be unique", UsersException::USERNAME_NOT_UNIQUE);
        }
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
     * @aclExceptionCode 9
     * @aclEventClass \occ2\inventar\User\models\events\UsersEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function editUser(ArrayHash $values)
    {
        $this->_acl(__FUNCTION__,(array) $values);
        try {
            $this->usersRepository->save($values);
            $this->fireEvent(static::EVENT_EDIT_USER, new UsersEvent($values, static::EVENT_EDIT_USER));
            return $values;
        } catch (UniqueConstraintViolationException $exc) {
            throw new UsersException("ERROR: Username must be unique", UsersException::USERNAME_NOT_UNIQUE);
        }
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
     * @aclExceptionCode 16
     * @aclEventClass \occ2\inventar\User\models\events\UsersEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function deleteUser(int $id)
    {
        $this->_acl(__FUNCTION__, ["user"=>$id], $id);
        $this->fireEvent(self::EVENT_DELETE_USER, new UsersEvent($this->getUser($id,true),self::EVENT_DELETE_USER));
        return $this->usersRepository->delete($id);
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
     * @aclExceptionCode 10
     * @aclEventClass \occ2\inventar\User\models\events\UsersEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function resetPassword(int $id)
    {
        $this->_acl(__FUNCTION__, ["user"=>$id], $id);
        $values = $this->getUser($id,true);
        $values->password = Random::generate($this->config["randomPasswordLength"]);
        $values->{Users::PASSWORD_HASH} = Passwords::hash($values->password);
        $values->{Users::PASSWORD_EXPIRATION} = $this->datetimeFactory->create();
        $this->fireEvent(self::EVENT_RESET_PASSWORD, new UsersEvent($values,self::EVENT_RESET_PASSWORD));
        unset($values->password);
        return $this->usersRepository->save($values);
    }

    /**
     * get list of system admins
     * @return ArrayHash[]
     */
    public function getAdmins(){
        $users=[];
        $ids = $this->rolesRepository
                    ->findBy([Roles::ROLE=>self::ADMIN_ROLE])
                    ->fetchPairs(Roles::ID,Roles::USER);
        foreach($ids as $userId){
            $users[$userId] = $this->getUser($userId);
        }
        return $users;
    }

    /*******************************************************************/
    /*                          private AREA                         */
    /*******************************************************************/

    /**
     * try to find and load user
     * @param string $username
     * @return \Nette\Database\ActiveRow
     * @throws AuthenticationException
     */
    private function loadUser($username,$test=false)
    {
        if(
            $test==true && 
            $username != $this->user->getIdentity()->{Users::USERNAME} &&
            !$this->user->isAllowed(static::ACL_RESOURCE_USER, AuthorizatorFacade::PRIVILEGE_WRITE)
        ) {
                throw new UsersException("ERROR: You are not allowed to load users data", UsersException::UNAUTHORIZED_USER_LOAD);
        }
        $user = $this->usersRepository->findOneBy([Users::USERNAME=>$username]);
        if (!$user) {
            $this->eventDispatcher->dispatch(AuthenticatorFacade::EVENT_USER_NOT_FOUND, new AuthenticationEvent([null, ILogger::DANGER], AuthenticatorFacade::EVENT_USER_NOT_FOUND));
            throw new AuthenticationException("ERROR: Invalid user", AuthenticationException::IDENTITY_NOT_FOUND);
        }
        return $user;
    }

    private function testPassword($user, $password)
    {
        if (!Passwords::verify($password, $user->{Users::PASSWORD_HASH})) {
            $this->eventDispatcher->dispatch(AuthenticatorFacade::EVENT_INVALID_CREDENTIAL, new AuthenticationEvent([$user->{Users::ID}, ILogger::DANGER], AuthenticatorFacade::EVENT_INVALID_CREDENTIAL));
            throw new AuthenticationException("ERROR: Invalid credential", AuthenticationException::INVALID_CREDENTIAL);
        }
        return;
    }
    
    private function comparePasswords($password, $repeated)
    {
        if ($password!==$repeated) {
            throw new UsersException("ERROR: Passwords not same", UsersException::PASSWORDS_NOT_SAME);
        }
        return;
    }
    
    private function savePassword($user, $password)
    {
        $_user = ArrayHash::from($user->toArray());
        $_user->{Users::PASSWORD_HASH} = Passwords::hash($password);
        $_user->{Users::PASSWORD_EXPIRATION} = $this->datetimeFactory->create()->modify($this->config["passwordExpiration"]);
        $this->usersRepository->save($_user);
        return;
    }
       
    private function setDefaultRoles(int $userId)
    {
        foreach ($this->config["defaultRoles"] as $role) {
            $this->rolesRepository->save([
                Roles::USER=>$userId,
                Roles::ROLE=>$role
            ]);
        }
        return;
    }

    protected function setDefaultConfig(int $userId)
    {
        foreach ($this->defaultUserConfig as $config) {
            $this->usersConfigRepository->save(ArrayHash::from([
                UsersConfig::USER=>$userId,
                UsersConfig::KEY=>$config[UsersConfig::KEY],
                UsersConfig::VALUE=>$config[UsersConfig::VALUE],
                UsersConfig::COMMENT=>$config[UsersConfig::COMMENT],
                UsersConfig::TYPE=>$config[UsersConfig::TYPE]
            ]));
        }
        return;
    }
}
