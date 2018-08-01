<?php
namespace occ2\model;

use Nette\Security\User;
use Symfony\Component\EventDispatcher\EventDispatcher;

trait TFacadePermissions{
    /**
     * @var \Nette\Security\User
     */
    protected $user;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $ed;

    /**
     * @var array
     */
    protected $annotationConfig=[];

    public static $defaultPrivilege="read";
    public static $defaultAclErrorMessage="base.error.notPermitted";
    public static $defaultAclErrorCode=403;

    /**
     * user setter
     * @param User $user
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * event dispatcher setter
     * @param EventDispatcher $ed
     * @return $this
     */
    public function setEventDispatcher(EventDispatcher $ed)
    {
        $this->ed = $ed;
        return $this;
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
    public function isAllowed(string $resource,string $privilege=null,string $exceptionClass=null,string $message=null,int $code=null,string $eventAnchor=null,string $eventClass=null,array $data=[])
    {
        $privilege = $privilege!=null ?: self::$defaultPrivilege;
        $message = $message!=null ?: self::$defaultAclErrorMessage;
        $code = $code!=null ?: self::$defaultAclErrorCode;
        $this->testUser();
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
    public function isLoggedIn(string $exceptionClass=null,string $message=null,int $code=null,string $eventAnchor=null,string $eventClass=null,array $data=[])
    {
        $this->testUser();
        $message = $message!=null ?: self::$defaultAclErrorMessage;
        $code = $code!=null ?: self::$defaultAclErrorCode;
        if(!$this->user->isLoggedIn()){
            $this->aclError($exceptionClass,$message,$code,$eventAnchor,$eventClass,$data);
        }
        return true;
    }

    /**
     * test if user is current user
     * @param int $userId
     * @param string $exceptionClass
     * @param string $message
     * @param int $code
     * @param string $eventAnchor
     * @param string $eventClass
     * @param array $data
     * @return boolean
     */
    public function isCurrentUser(int $userId,string $exceptionClass=null,string $message=null,int $code=null,string $eventAnchor=null,string $eventClass=null,array $data=[])
    {
        $this->testUser();
        $message = $message!=null ?: self::$defaultAclErrorMessage;
        $code = $code!=null ?: self::$defaultAclErrorCode;
        if(!$this->user->isLoggedIn() || $this->user->getId() != $userId){
            $this->aclError($exceptionClass,$message,$code,$eventAnchor,$eventClass,$data);
        }
        return true;
    }

    protected function aclError($exceptionClass,$message,$code,$eventAnchor,$eventClass,$data)
    {
        $this->testEvent();
        if($eventAnchor!=null && $eventClass!=null){
            $this->ed->dispatch($eventAnchor, new $eventClass($data));
        }
        if($exceptionClass!=null){
            throw new $exceptionClass($message,$code);
        } else {
            return false;
        }
    }

    protected function getAnnotationConfig()
    {
        $classType = ClassType::from(static::class);
        foreach($classType->getMethods() as $method){
            $name = Strings::firstLower($method->getName());
            $this->annotationConfig[$name] = $method->getAnnotations();
        }
        return;
    }

    protected function testUser()
    {
        if(!$this->user instanceof Nette\Security\User){
            throw new \Exception("ERROR: \Nette\Security\User must be set");
        }
    }

    protected function testEvent()
    {
        if(!$this->ed instanceof EventDispatcher){
            throw new \Exception("ERROR: \Symfony\Component\EventDispatcher\EventDispatcher must be set");
        }
    }

    /**
     * @param string $method
     * @param array $data
     * @param int $id
     * @return boolean
     */
    protected function _acl($method,$data=[],$id=null)
    {
        if(count($this->annotationConfig)){
            $this->getAnnotationConfig();
        }
        if(isset($this->annotationConfig[$method])){
            $config = $this->annotationConfig[$method];

            if(isset($config["currentUser"]) && isset($config["aclResource"])){
                if($id!=null){
                    if(
                        !$this->isCurrentUser($id) &&
                        !$this->isAllowed(
                            $config["aclResource"][0],
                            isset($config["aclPrivilege"]) ? $config["aclPrivilege"][0] : self::$defaultPrivilege
                        )
                    ){
                        $this->aclError(
                            isset($config["aclExceptionClass"]) ? $config["aclExceptionClass"][0] : '\Exception',
                            isset($config["aclExceptionMessage"]) ? $config["aclExceptionMessage"][0] : self::$defaultAclErrorMessage,
                            isset($config["aclExceptionCode"]) ? $config["aclExceptionCode"][0] : self::$defaultAclErrorCode,
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
                    isset($config["aclPrivilege"]) ? $config["aclPrivilege"][0] : self::$defaultPrivilege,
                    isset($config["aclExceptionClass"]) ? $config["aclExceptionClass"][0] : '\Exception',
                    isset($config["aclExceptionMessage"]) ? $config["aclExceptionMessage"][0] : self::$defaultAclErrorMessage,
                    isset($config["aclExceptionCode"]) ? $config["aclExceptionCode"][0] : self::$defaultAclErrorCode,
                    isset($config["aclEventAnchor"]) ? $config["aclEventAnchor"][0] : null,
                    isset($config["aclEventClass"]) ? $config["aclEventClass"][0] : null,
                    $data
                );
            } elseif(isset($config["currentUser"]) && !isset($config["aclResource"])){
                if($id!=null){
                    return $this->isCurrentUser(
                        $id,
                        isset($config["aclExceptionClass"]) ? $config["aclExceptionClass"][0] : '\Exception',
                        isset($config["aclExceptionMessage"]) ? $config["aclExceptionMessage"][0] : self::$defaultAclErrorMessage,
                        isset($config["aclExceptionCode"]) ? $config["aclExceptionCode"][0] : self::$defaultAclErrorCode,
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

