<?php
namespace app\Base\models\facades;

use app\User\models\exceptions\PermissionException;
use app\User\events\PermissionEvent;
use app\Base\events\Event;
use Nette\Utils\Strings;
use Nette\Reflection\ClassType;

/**
 * Model parent for facades with permissions
 * facade over a repository
 * depends on
 * contributte/event-dispatcher
 * nette/utils
 * nette/security
 * nette/reflection
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
abstract class BaseFacade extends AbstractFacade
{
    const DEFAULT_PRIVILEGE="read";
    const DEFAULT_ACL_MESSAGE="base.error.403";

    /**
     * @var array
     */
    protected $annotationConfig;

    /**
     * @var string
     */
    public $aclExceptionClass=PermissionException::class;

    /**
     * @var string
     */
    public $aclEventClass=PermissionEvent::class;

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
     * call private method as public, but with acl test
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method,array $arguments)
    {
        if(method_exists($this, $method)) {
            if($this->user instanceof \Nette\Security\User){
                $this->acl($method, $arguments);
            }
            return call_user_func_array([$this,$method],$arguments);
        }
    }
    /**
     * run acl test
     * @param string $method
     * @param mixed $data
     * @return void
     */
    protected function acl($method,$data)
    {
        if(isset($this->annotationConfig[$method]["ACL"]) && !empty($this->annotationConfig[$method]["ACL"])){
            $config = $this->annotationConfig[$method]["ACL"][0];
            if(isset($config["loggedIn"])){
                $this->loggedIn($config);
                return;
            } elseif(isset($config["resource"])){
                $this->loggedIn($config);
                $this->isAllowed($config,$data);
                return;
            } else {
                return;
            }
        }
    }

    /**
     * test if logged in
     * @param array $config
     * @return void
     */
    protected function loggedIn(array $config){
        if(!$this->user->isLoggedIn()){
            $this->aclError($config, PermissionException::NOT_LOGGED_IN);
        }
        return;
    }

    /**
     * test if allowed
     * @param array $config
     * @param mixed $data
     * @return void
     */
    protected function isAllowed(array $config,$data){
        if(!$this->user->isAllowed(
            $config["resource"],
            isset($config["resource"]) ? $config["resource"] : self::DEFAULT_PRIVILEGE
        )){
            $this->aclError($config, PermissionException::OPERATION_NOT_PERMITTED, $data);
        }
        return;
    }

    /**
     * throw acl error
     * @param array $config
     * @param int $defaultCode
     * @param mixed $data
     * @return mixed
     * @throws mixed
     */
    protected function aclError(array $config,int $defaultCode,$data=null)
    {
        if(isset($config["event"])){
            $eventClass = isset($config["eventClass"]) ? $config["eventClass"] : $this->aclEventClass;
            return $this->on($config["event"], new $eventClass($data,$config["event"]));
        } else {
            $exceptionClass = isset($config["exception"]) ? $config["exception"] : $this->aclExceptionClass;
            $message = isset($config["message"]) ? $config["message"] : self::DEFAULT_ACL_MESSAGE;
            $code = isset($config["code"]) ? $config["code"] : $defaultCode;
            throw new $exceptionClass($message,$code);
        }
    }
}
