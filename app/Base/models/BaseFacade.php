<?php
namespace app\Base\models;

use app\Base\events\Event;
use Nette\Utils\Strings;
use Nette\Reflection\ClassType;
use Nette\Security\User;
use Contributte\Utils\DatetimeFactory;
use Contributte\EventDispatcher\EventDispatcher;

/**
 * Model parent of all facades
 * facade over a repository
 * depends on
 * contributte/event-dispatcher
 * contributte/utils
 * nette/security
 * nette/database
 * nette/reflection
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
abstract class BaseFacade
{
    const DEFAULT_PRIVILEGE="read";
    const DEFAULT_ACL_ERROR_MESSAGE="ERROR: Operation not permitted";
    const DEFAULT_ACL_ERROR_CODE=403;

    /**
     * @var \Contributte\EventDispatcher\EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var \Nette\Security\User
     */
    protected $user;

    /**
     * @var \Contributte\Utils\DatetimeFactory
     */
    protected $datetimeFactory;

    /**
     * @var array
     */
    protected $annotationConfig;

    /**
     * @var array
     */
    public $config;

    /**
     * @param \Contributte\Utils\DatetimeFactory
     * @param \Nette\Security\User $user
     * @param \Contributte\EventDispatcher\EventDispatcher $eventDispatcher
     * @param array $config
     * @return void
     */
    public function __construct(DatetimeFactory $datetimeFactory, User $user, EventDispatcher $eventDispatcher, $config=[])
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->user = $user;
        $this->config = $config;
        $this->datetimeFactory = $datetimeFactory;
        $this->getAnnotationConfig();
        return;
    }

    /**
     * fire event
     * @param string $anchor
     * @param \occ2\model\Event $event
     * @return void
     * @deprecated since version 1.1
     */
    public function fireEvent(string $anchor, Event $event=null)
    {
        return $this->eventDispatcher->dispatch($anchor, $event);
    }

    /**
     * alias for fire event
     * @param string $anchor
     * @param Event $event
     * @return void
     */
    public function on(string $anchor, Event $event=null)
    {
        return $this->eventDispatcher->dispatch($anchor, $event);
    }

    /**
     * test permission allowed
     * @param string $resource
     * @param string $privilege
     * @param string $exceptionClass
     * @param string $message
     * @param int $code
     * @param string $eventAnchor
     * @param string $eventClass
     * @param array $data
     * @return bool
     */
    public function isAllowed(string $resource,string $privilege=self::DEFAULT_PRIVILEGE,string $exceptionClass=null,string $message=self::DEFAULT_ACL_ERROR_MESSAGE,int $code=self::DEFAULT_ACL_ERROR_CODE,string $eventAnchor=null,string $eventClass=null,array $data=[])
    {
        if(!$this->user->isAllowed($resource, $privilege)){
            $this->aclError($exceptionClass,$message,$code,$eventAnchor,$eventClass,$data);
        }
        return true;
    }

    /**
     * test user logged in
     * @param string $exceptionClass
     * @param string $message
     * @param int $code
     * @param string $eventAnchor
     * @param string $eventClass
     * @param array $data
     * @return boolean
     */
    public function isLoggedIn(string $exceptionClass=null,string $message=self::DEFAULT_ACL_ERROR_MESSAGE,int $code=self::DEFAULT_ACL_ERROR_CODE,string $eventAnchor=null,string $eventClass=null,array $data=[])
    {
        if(!$this->user->isLoggedIn()){
            $this->aclError($exceptionClass,$message,$code,$eventAnchor,$eventClass,$data);
        }
        return true;
    }

    /**
     * test userId is id of current user
     * @param int $userId
     * @param string $exceptionClass
     * @param string $message
     * @param int $code
     * @param string $eventAnchor
     * @param string $eventClass
     * @param array $data
     * @return boolean
     */
    public function isCurrentUser(int $userId,string $exceptionClass=null,string $message=self::DEFAULT_ACL_ERROR_MESSAGE,int $code=self::DEFAULT_ACL_ERROR_CODE,string $eventAnchor=null,string $eventClass=null,array $data=[])
    {
        if(!$this->user->isLoggedIn() || $this->user->getId() != $userId){
            $this->aclError($exceptionClass,$message,$code,$eventAnchor,$eventClass,$data);
        }
        return true;
    }

    /**
     * throw acl error
     * @param string $exceptionClass
     * @param string $message
     * @param int $code
     * @param string $eventAnchor
     * @param string $eventClass
     * @param type $data
     * @return boolean
     * @throws \Exception
     */
    protected function aclError(string $exceptionClass,string $message,int $code,string $eventAnchor,string $eventClass,$data)
    {
        if($eventAnchor!=null && $eventClass!=null){
            $this->on($eventAnchor, new $eventClass($data));
        }
        if($exceptionClass!=null){
            throw new $exceptionClass($message,$code);
        } else {
            return false;
        }
    }

    /**
     * @return void
     */
    protected function getAnnotationConfig()
    {
        $classType = ClassType::from(static::class);
        foreach($classType->getMethods() as $method){
            $name = Strings::firstLower($method->getName());
            $this->annotationConfig[$name] = $method->getAnnotations();
        }
        return;
    }

    /**
     * @param string $method
     * @param array $data
     * @param int $id
     * @return boolean
     */
    protected function _acl($method,$data=[],$id=null)
    {
        if(isset($this->annotationConfig[$method])){
            $config = $this->annotationConfig[$method];

            if(isset($config["currentUser"]) && isset($config["aclResource"])){
                if($id!=null){
                    if(
                        !$this->isCurrentUser($id) &&
                        !$this->isAllowed(
                            $config["aclResource"][0],
                            isset($config["aclPrivilege"]) ? $config["aclPrivilege"][0] : static::DEFAULT_PRIVILEGE
                        )
                    ){
                        $this->aclError(
                            isset($config["aclExceptionClass"]) ? $config["aclExceptionClass"][0] : '\Exception',
                            isset($config["aclExceptionMessage"]) ? $config["aclExceptionMessage"][0] : static::DEFAULT_ACL_ERROR_MESSAGE,
                            isset($config["aclExceptionCode"]) ? $config["aclExceptionCode"][0] : static::DEFAULT_ACL_ERROR_CODE,
                            isset($config["aclEventAnchor"]) ? $config["aclEventAnchor"][0] : null,
                            isset($config["aclEventClass"]) ? $config["aclEventClass"][0] : null,
                            $data
                        );
                    } else {
                        return true;
                    }
                }
            } elseif(!isset($config["currentUser"]) && isset($config["aclResource"])){
                return $this->isAllowed(
                    $config["aclResource"][0],
                    isset($config["aclPrivilege"]) ? $config["aclPrivilege"][0] : static::DEFAULT_PRIVILEGE,
                    isset($config["aclExceptionClass"]) ? $config["aclExceptionClass"][0] : '\Exception',
                    isset($config["aclExceptionMessage"]) ? $config["aclExceptionMessage"][0] : static::DEFAULT_ACL_ERROR_MESSAGE,
                    isset($config["aclExceptionCode"]) ? $config["aclExceptionCode"][0] : static::DEFAULT_ACL_ERROR_CODE,
                    isset($config["aclEventAnchor"]) ? $config["aclEventAnchor"][0] : null,
                    isset($config["aclEventClass"]) ? $config["aclEventClass"][0] : null,
                    $data
                );                
            } elseif(isset($config["currentUser"]) && !isset($config["aclResource"])){
                if($id!=null){
                    return $this->isCurrentUser(
                        $id,
                        isset($config["aclExceptionClass"]) ? $config["aclExceptionClass"][0] : '\Exception',
                        isset($config["aclExceptionMessage"]) ? $config["aclExceptionMessage"][0] : static::DEFAULT_ACL_ERROR_MESSAGE,
                        isset($config["aclExceptionCode"]) ? $config["aclExceptionCode"][0] : static::DEFAULT_ACL_ERROR_CODE,
                        isset($config["aclEventAnchor"]) ? $config["aclEventAnchor"][0] : null,
                        isset($config["aclEventClass"]) ? $config["aclEventClass"][0] : null,
                        $data
                    );
                }
            } else{
                return null;
            }
        }
    }
}
