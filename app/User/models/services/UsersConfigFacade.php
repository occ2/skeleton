<?php
namespace occ2\inventar\User\models;

use occ2\model\Model as BaseModel;
use Contributte\Utils\DatetimeFactory;
use Nette\Utils\ArrayHash;
use Nette\Security\User;
use Contributte\EventDispatcher\EventDispatcher;
use occ2\inventar\User\models\repositories\UsersConfig;
use occ2\inventar\User\models\exceptions\UsersException;
use occ2\inventar\User\models\events\UsersConfigEvent;

/**
 * UsersSettingsFacade
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
final class UsersConfigFacade extends BaseModel
{
    const ACL_RESOURCE_USER="users",
          USER_CONFIG_NOTIFY_CHANGE_CONFIG="userNotifyChangeConfig",
          USER_NOTIFY_RESET_CONFIG="userNotifyResetConfig",
          USER_NOTIFY_RELOAD_CONFIG="userNotifyReloadConfig",
          EVENT_UNAUTHORIZED_CONFIG_LOAD="user.manager.error",
          EVENT_RESET_CONFIG="user.config.reset",
          EVENT_RELOAD_CONFIG="user.config.reload";

    /**
     * @var array
     */
    private $defaultUserConfig;

    /**
     * @var \occ2\inventar\User\models\repositories\UsersConfig
     */
    private $usersConfigRepository;

    /**
     * @param DatetimeFactory $datetimeFactory
     * @param User $user
     * @param EventDispatcher $eventDispatcher
     * @param UsersConfig $usersConfigRepository
     * @param array $config
     * @param array $defaultUserConfig
     * @return void
     */
    public function __construct(
        DatetimeFactory $datetimeFactory,
        User $user,
        EventDispatcher $eventDispatcher,
        UsersConfig $usersConfigRepository,
        $config = [],
        $defaultUserConfig=[])
    {
        $this->usersConfigRepository = $usersConfigRepository;
        $this->defaultUserConfig = $defaultUserConfig;
        parent::__construct($datetimeFactory, $user, $eventDispatcher, $config);
    }

    /**
     * get user config
     * @param int $usersId
     * @return \Nette\Database\Table\Selection
     * @throws UsersException
     *
     * @currentUser true
     * @aclResource users
     * @aclExceptionClass \occ2\inventar\User\models\exceptions\UsersException
     * @aclExceptionMessage "ERROR: You are not allowed to view users config"
     * @aclExceptionCode 12
     * @aclEventClass \occ2\inventar\User\models\events\UsersEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function getUserConfig(int $usersId=null)
    {
        $this->_acl(__FUNCTION__, ["user"=>$usersId], $usersId);
        if($usersId==null) {
            return $this->usersConfigRepository->findAll();
        } else {
            return $this->usersConfigRepository->findBy([UsersConfig::USER=>$usersId]);
        }
    }

    /**
     * search user config
     * @param int $usersId
     * @param string $key
     * @return ArrayHash
     */
    public function searchUserConfig(int $usersId,string $key,$useSession=true)
    {
        if(isset($this->user->getIdentity()->config[$key]) && $useSession==true){
            return $this->user->getIdentity()->config[$key];
        } else {
            $c = $this->usersConfigRepository->findOneBy([
                UsersConfig::KEY=>$key,
                UsersConfig::USER=>$usersId
            ]);
            if(!$c){
                foreach($this->defaultUserConfig as $defaultConfig){
                     if($defaultConfig[UsersConfig::KEY]==$key){
                         return $defaultConfig[UsersConfig::VALUE];
                     }
                }
            } else {
                return $c->{UsersConfig::VALUE};
            }
        }
    }

    /**
     * reset users config to defaults
     * @param int $userId
     * @return void
     * @throws UsersException
     *
     * @currentUser true
     * @aclResource users
     * @aclPrivilege write
     * @aclExceptionClass \occ2\inventar\User\models\exceptions\UsersException
     * @aclExceptionMessage "ERROR: Unauthorized config reset"
     * @aclExceptionCode 13
     * @aclEventClass \occ2\inventar\User\models\events\UsersEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function resetConfig(int $userId)
    {
        $this->_acl(__FUNCTION__, ["user"=>$userId], $userId);
        $this->fireEvent(static::EVENT_RESET_CONFIG, new UsersConfigEvent(["user"=>$userId], static::EVENT_RESET_CONFIG));
        return $this->setDefaultConfig($userId);
    }

    /**
     * update config
     * @param int $id
     * @param mixed $value
     * @return void
     * @throws UsersException
     *
     * @currentUser true
     * @aclResource users
     * @aclPrivilege write
     * @aclExceptionClass \occ2\inventar\User\models\exceptions\UsersException
     * @aclExceptionMessage "ERROR: Unauthorized config update"
     * @aclExceptionCode 14
     * @aclEventClass \occ2\inventar\User\models\events\UsersEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function updateConfig(int $id,$value)
    {
        $config = $this->usersConfigRepository->find($id);
        $this->_acl(__FUNCTION__, ["user"=>$config->{UsersConfig::USER}], $config->{UsersConfig::USER});
        if($config->{UsersConfig::TYPE}=="array" && is_array($value)){
            $value = implode(",", $value);
        } elseif ($config->{UsersConfig::TYPE}=="bool") {
            $value = (bool) $value;
        }
        return $this->usersConfigRepository->change($id, UsersConfig::VALUE, $value);
    }

    /**
     * reload not saved config settings to default position
     * @param int $userId
     * @return type
     * @throws UsersException
     *
     * @currentUser true
     * @aclResource users
     * @aclPrivilege write
     * @aclExceptionClass \occ2\inventar\User\models\exceptions\UsersException
     * @aclExceptionMessage "ERROR: Unauthorized config reload"
     * @aclExceptionCode 15
     * @aclEventClass \occ2\inventar\User\models\events\UsersEvent
     * @aclEventAnchor "user.manager.error"
     */
    public function reloadConfig(int $userId)
    {
        $this->_acl(__FUNCTION__, ["user"=>$userId], $userId);
        $this->fireEvent(static::EVENT_RELOAD_CONFIG, new UsersConfigEvent(["user"=>$userId], static::EVENT_RELOAD_CONFIG));
        foreach($this->defaultUserConfig as $defaultConfig){
            $dbConfig = $this->usersConfigRepository->findOneBy([
                UsersConfig::USER=>$userId,
                UsersConfig::KEY=>$defaultConfig[UsersConfig::KEY]
            ]);
            if(!$dbConfig){
                $this->usersConfigRepository->save(ArrayHash::from([
                    UsersConfig::USER=>$userId,
                    UsersConfig::KEY=>$defaultConfig[UsersConfig::KEY],
                    UsersConfig::VALUE=>$defaultConfig[UsersConfig::VALUE],
                    UsersConfig::COMMENT=>$defaultConfig[UsersConfig::COMMENT],
                    UsersConfig::TYPE=>$defaultConfig[UsersConfig::TYPE]
                ]));
            }
        }
        return;
    }
    
    protected function setDefaultConfig(int $userId)
    {
        $this->clearConfig($userId);
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

    protected function clearConfig(int $userId)
    {
        return $this->usersConfigRepository->findBy([
            UsersConfig::USER=>$userId
        ])->delete();
    }
}