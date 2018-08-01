<?php
namespace occ2\inventar\presenters;

use Nette\Application\UI\Presenter as NPresenter;
use occ2\inventar\events\BaseEvent;
use occ2\flashes\TFlashMessage;
use Nette\Localization\ITranslator;
use Nette\Reflection\ClassType;
use Nette\Utils\Strings;
use Nette\Http\IResponse;
use Nette\Utils\ArrayHash;

/**
 * parent of all presenters
 *
 * @author Milan Onderka
 * @version 1.0.0
 */
abstract class AbstractPresenter extends NPresenter
{
    use TFlashMessage;
    
    const AUTHENTICATOR="authenticator",
          AUTHORIZATOR="authorizator",
          ICON_SUCCESS="check-circle",
          ICON_DANGER="times-circle",
          ICON_WARNING="exclamation-circle",
          ICON_INFO="info-circle",
          STATUS_SUCCESS="success",
          STATUS_DANGER="danger",
          STATUS_WARNING="warning",
          STATUS_INFO="info",
          THIS="this",
          ACTION_DEFAULT="default",
          BREADCRUMBS="breadcrumbs",
          SIGN_IN_LINK=":User:Main:signIn";
        
    /** @persistent */
    public $backlink = '';
        
    /**
     * @inject
     * @var \occ2\inventar\controls\INavbar
     */
    public $navbarFactory;

    /**
     * @inject
     * @var \occ2\breadcrumbs\IBreadcrumbs
     */
    public $breadcrumbsFactory;
    
    /**
     * @inject @var \Contributte\EventDispatcher\EventDispatcher
     */
    public $eventDispather;
    
    /**
     * @inject @var \Nette\Localization\ITranslator
     */
    public $translator;
    
    /** @persistent */
    public $locale;
    
    /**
     * @var string
     */
    protected $title;

    /**
     * @var array
     */
    protected $actionsConfig=[];

    /**
     * @var array
     */
    protected $handlersConfig=[];

    /**
     * @var string
     */
    public static $titlePrefix="INVENTAR.CLOUD - ";

    /**
     * @var string
     */
    public static $iconPrefix="fas fa-";

    /**
     * @var bool
     */
    public static $allowAnnotationTitles=true;

    /**
     * @var bool
     */
    public static $allowAnnotationAcl=true;
    
    /**
     * startup processes
     * @return void
     */
    public function startup()
    {
        if(static::$allowAnnotationAcl || static::$allowAnnotationTitles){
            $this->getAnnotationsConfig();
        }
        if(static::$allowAnnotationAcl){
            $this->annotationsAcl();
        }

        $this->user->setAuthenticator($this->context->getService(static::AUTHENTICATOR));
        $this->user->setAuthorizator($this->context->getService(static::AUTHORIZATOR));
        $this->payload->isModal = false;
        return parent::startup();
    }
    
    /**
     * @return \occ2\inventar\controls\Navbar
     */
    public function createComponentNavbar()
    {
        return $this->navbarFactory->create();
    }

    /**
     * @return \occ2\breadcrumbs\Breadcrumbs
     */
    public function createComponentBreadcrumbs()
    {
        $breadcrumbs = $this->breadcrumbsFactory->create();
        $breadcrumbs->addItem("home","base.breadcrumbs.home", $this->link(":User:Main:default"),false);
        return $breadcrumbs;
    }
    
    /**
     * fire event
     * @param string $anchor
     * @param \occ2\inventar\events\BaseEvent $event
     * @return void
     */
    public function fireEvent(string $anchor, BaseEvent $event=null)
    {
        //return $this->eventDispather->dispatch($anchor, $event);
    }
    
    /**
     * add page title before render
     * @return void
     */
    public function beforeRender()
    {
        parent::beforeRender();
        if(static::$allowAnnotationTitles){
            $this->annotationsTitle();
        }
        
        $this->template->title = static::$titlePrefix . $this->translator->translate($this->title);
        $this->template->locale = $this->locale;
        if ($this->isAjax()) {
            $this->reload();
        }
        return;
    }

    /**
     * translation simplifier
     * @param string $text
     * @return string
     */
    public function text(string $text) : string
    {
        if($this->translator instanceof ITranslator){
            return $this->translator->translate($text);
        } else {
            return $text;
        }
    }

    /**
     * alias for translator simplifier
     * @param string $text
     * @return string
     */
    public function _(string $text) : string
    {
        return $this->text($text);
    }

    public function reload(){
        $this->redrawControl('title');
        $this->redrawControl('flashes');
        $this->redrawControl('flashesSnippet');
        $this->redrawControl('breadcrumbs');
        $this->redrawControl('navbar');
        $this->redrawControl('content');
    }

    protected function getAnnotationsConfig()
    {
        $this->getActionsConfig();
        $this->getHandlersConfig();
    }

    protected function getActionsConfig()
    {
        $classType = ClassType::from(static::class);
        foreach($classType->getMethods() as $method){
            if(Strings::startsWith($method->getName(), "action")){
                $name = Strings::firstLower(str_replace("action", "", $method->getName()));
                $this->actionsConfig[$name] = $method->getAnnotations();
            }
        }
        return;
    }

    protected function getHandlersConfig()
    {
        $classType = ClassType::from(static::class);
        foreach($classType->getMethods() as $method){
            if(Strings::startsWith($method->getName(), "handle")){
                $name = Strings::firstLower(str_replace("handle", "", $method->getName()));
                $this->handlersConfig[$name] = $method->getAnnotations();
            }
        }
        return;
    }

    protected function annotationsTitle()
    {
        if(array_key_exists($this->getAction(),$this->actionsConfig)){
            if(isset($this->actionsConfig[$this->getAction()]["title"])){
                $this->title = $this->actionsConfig[$this->getAction()]["title"][0];
            }
        }
    }

    protected function annotationsAcl()
    {
        if(isset($this->actionsConfig[$this->getAction()]["acl"])){
            $this->testAcl($this->actionsConfig[$this->getAction()]["acl"][0]);
        }
        if(count($this->getSignal())>1 && isset($this->handlersConfig[$this->getSignal()[1]]["acl"])){
            $this->testAcl($this->handlersConfig[$this->getSignal()[1]]["acl"][0]);
        }
    }

    protected function testAcl(ArrayHash $config)
    {
        if(isset($config->resource)){
            $this->testLoggedIn(true, $config);
            $this->testPrivilege($config->resource, $config);
        } elseif(isset($config->loggedIn)){
            $this->testLoggedIn($config->loggedIn, $config);
        }
        return;
    }

    protected function testLoggedIn(bool $loggedIn,ArrayHash $config)
    {
        if($loggedIn==true){
            if(!$this->user->isLoggedIn()){
                (!isset($config->errorMsg) || !isset($config->errorRedirect)) ? : $this->flashMessage($this->_($config->errorMsg), static::STATUS_DANGER);
                isset($config->errorRedirect) ? $this->redirect($config->errorRedirect,['backlink' => $this->storeRequest()]) : $this->redirect(static::SIGN_IN_LINK,['backlink' => $this->storeRequest()]);
            }
        }
        return;
    }

    protected function testPrivilege(string $resource,ArrayHash $config)
    {
        if(isset($config->privilege)){
            if(!$this->user->isAllowed($resource, $config->privilege)){
                (!isset($config->errorMsg) || !isset($config->errorRedirect)) ? : $this->flashMessage($this->_($config->errorMsg), static::STATUS_DANGER);
                isset($config->errorRedirect) ? $this->redirect($config->errorRedirect) : $this->error(NULL, IResponse::S403_FORBIDDEN);
            }
        }
        return;
    }
}
