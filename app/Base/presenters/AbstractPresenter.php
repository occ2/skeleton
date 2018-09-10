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

namespace app\Base\presenters;

use app\Base\events\Event as BaseEvent;
use app\User\events\data\PermissionEvent;
use app\Base\traits\TFlashMessage;
use Nette\Caching\Cache;
use Nette\Reflection\ClassType;
use Nette\Application\UI\Presenter as NPresenter;
use Nette\Localization\ITranslator;
use Nette\Utils\Strings;
use Nette\Utils\ArrayHash;

/**
 * parent of all presenters
 *
 * @author Milan Onderka
 * @version 1.1.0
 */
abstract class AbstractPresenter extends NPresenter
{
    use TFlashMessage;

    const CACHE_PREFIX="presenter";

    const AUTHENTICATOR="userAuthenticator",
          AUTHORIZATOR="userAuthorizator",
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
          NAVBAR="navbar",
          SIGN_IN_LINK=":User:Sign:in",
          HOMEPAGE_LINK=":User:Main:default",
          ACL_ERROR_LINK=":User:Main:default",
          DEFAULT_ACL_MESSAGE="base.error.403",
          DEFAULT_PRIVILEGE="read",
          DEFAULT_LANG="cz";
        
    /** @persistent */
    public $backlink = '';
        
    /**
     * @inject @var \app\Base\factories\INavbar
     */
    public $navbarFactory;

    /**
     * @inject @var \app\Base\factories\IBreadcrumbs
     */
    public $breadcrumbsFactory;
    
    /**
     * @inject @var \Contributte\EventDispatcher\EventDispatcher
     */
    public $ed;
    
    /**
     * @inject @var \Nette\Localization\ITranslator
     */
    public $translator;

    /**
     * @inject @var \Contributte\Cache\ICacheFactory
     */
    public $cacheFactory;
    
    /** @persistent */
    public $locale;
    
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $appendTitle = null;

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
    public static $titlePrefix="SKELETON - ";

    /**
     * @var string
     */
    public static $iconPrefix="fas fa-";

    /**
     * @var array
     */
    public static $snippetList=[
        'title',
        'flashes',
        'flashesSnippet',
        'breadcrumbs',
        'navbar',
        'content'
    ];

    /**
     * @var string
     */
    public $aclEventClass=PermissionEvent::class;

    /**
     * @inject @var \Nette\Caching\IStorage
     */
    public $storage;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var ClassType
     */
    protected $classType;

    /**
     * startup processes
     * @return void
     */
    public function startup()
    {
        $this->cache = $this->cacheFactory->create(static::CACHE_PREFIX);
        $this->classType = ClassType::from($this);
        $this->getAnnotationsConfig();
        $this->user->setAuthenticator($this->context->getService(static::AUTHENTICATOR));
        $this->user->setAuthorizator($this->context->getService(static::AUTHORIZATOR));
        $this->annotationsAcl();
        $this->annotationsTitle();
        parent::startup();
        return;
    }
    
    /**
     * @return \app\Base\controls\Navbar\Navbar
     */
    public function createComponentNavbar()
    {
        return $this->navbarFactory->create();
    }

    /**
     * @return \app\Base\controls\Breadcrumbs\Breadcrumbs
     */
    public function createComponentBreadcrumbs()
    {
        $breadcrumbs = $this->breadcrumbsFactory->create();
        $this->addComponent($breadcrumbs, static::BREADCRUMBS);
        $breadcrumbs->addItem("home","base.breadcrumbs.home", static::HOMEPAGE_LINK,false);
        $breadcrumbs->setConfig($this->actionsConfig);
        return $breadcrumbs;
    }

    /**
     * fire event
     * @param string $anchor
     * @param BaseEvent $event
     * @return mixed
     */
    public function on(string $anchor, BaseEvent $event=null)
    {
        return $this->ed->dispatch($anchor, $event);
    }
    
    /**
     * add page title before render
     * @return void
     */
    public function beforeRender()
    {
        parent::beforeRender();
        if($this->appendTitle==null){
            $this->template->title = static::$titlePrefix . $this->translator->translate($this->title);
        } else {
            $this->template->title = static::$titlePrefix . $this->translator->translate($this->title) . " " . $this->appendTitle;
        }
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
    public function _(string $text) : string
    {
        if($this->translator instanceof ITranslator){
            return $this->translator->translate($text);
        } else {
            return $text;
        }
    }

    /**
     * reload all, one or more snippets
     * @param array | string | null $snippets
     * @return void
     */
    public function reload($snippets=null) {
        if($snippets==null){
            foreach(self::$snippetList as $snippet){
                $this->redrawControl($snippet);
            }
        } elseif (is_array($snippets)) {
            foreach($snippets as $snippet){
                $this->redrawControl($snippet);
            }
        } else {
            $this->redrawControl($snippets);
        }
        return;
    }

    /**
     * @return void
     */
    protected function getAnnotationsConfig()
    {
        $this->getActionsConfig();
        $this->getHandlersConfig();
        return;
    }

    /**
     * @return void
     */
    protected function getActionsConfig()
    {
        foreach($this->classType->getMethods() as $method){
            if(Strings::startsWith($method->getName(), "action")){
                $name = Strings::firstLower(str_replace("action", "", $method->getName()));
                $this->actionsConfig[$name] = $this->cache->load(
                    $this->classType->getName() . ".action." . $name
                );
                if($this->actionsConfig[$name]===null){
                    $this->actionsConfig[$name] = $method->getAnnotations();
                    $this->cache->save(
                        $this->classType->getName() . ".action." . $name,
                        $this->actionsConfig[$name],
                        [
                            Cache::FILES => $this->classType->getFileName()
                        ]
                    );
                }
            }
        }
        return;
    }

    /**
     * @return void
     */
    protected function getHandlersConfig()
    {
        foreach($this->classType->getMethods() as $method){
            if(Strings::startsWith($method->getName(), "handle")){
                $name = Strings::firstLower(str_replace("handle", "", $method->getName()));
                $this->handlersConfig[$name] = $this->cache->load(
                    $this->classType->getName() . ".handle." . $name
                );
                if($this->handlersConfig[$name]===null){
                    $this->handlersConfig[$name] = $method->getAnnotations();
                    $this->cache->save(
                        $this->classType->getName() . ".handle." . $name,
                        $this->handlersConfig[$name],
                        [
                            Cache::FILES => $this->classType->getFileName()
                        ]);
                }
            }
        }
        return;
    }

    /**
     * @return void
     */
    protected function annotationsTitle()
    {
        if(array_key_exists($this->getAction(),$this->actionsConfig)){
            if(isset($this->actionsConfig[$this->getAction()]["title"])){
                $this->title = $this->actionsConfig[$this->getAction()]["title"][0];
            } else {
                $this->title = "";
            }
        }
        return;
    }

    /**
     * @return void
     */
    protected function annotationsAcl()
    {
        if(isset($this->actionsConfig[$this->getAction()]["acl"])){
            $this->acl($this->actionsConfig[$this->getAction()]["acl"][0]);
        }
        $signals = $this->getSignal();
        if($signals!=null && count($signals)>1 && isset($this->handlersConfig[$signals[1]]["acl"])){
            $this->acl($this->handlersConfig[$signals[1]]["acl"][0]);
        }
        return;
    }

    /**
     * run acl test
     * @param ArrayHash $config
     * @return void
     */
    protected function acl(ArrayHash $config)
    {
        if(isset($config["loggedIn"])){
            $this->loggedIn($config);
            return;
        } elseif(isset($config["resource"])){
            $this->loggedIn($config);
            $this->isAllowed($config);
            return;
        } else {
           return;
        }
    }

    /**
     * test if logged in
     * @param ArrayHash $config
     * @return void
     */
    protected function loggedIn(ArrayHash $config){
        if(!$this->user->isLoggedIn()){
            $this->aclError($config);
        }
        return;
    }

    /**
     * test if allowed
     * @param ArrayHash $config
     * @return void
     */
    protected function isAllowed(ArrayHash $config){
        if(!$this->user->isAllowed(
            $config["resource"],
            isset($config["resource"]) ? $config["resource"] : self::DEFAULT_PRIVILEGE
        )){
            $this->aclError($config);
        }
        return;
    }

    /**
     * throw acl error
     * @param ArrayHash $config
     * @return void
     */
    protected function aclError(ArrayHash $config)
    {
        if(isset($config["event"])){
            $eventClass = isset($config["eventClass"]) ? $config["eventClass"] : $this->aclEventClass;
            $this->on($config["event"], new $eventClass($this,$config["event"]));
        } else {
            $redirect = isset($config["redirect"]) ? $config["redirect"] : self::ACL_ERROR_LINK;
            $message = isset($config["message"]) ? $config["message"] : self::DEFAULT_ACL_MESSAGE;
            $this->flashMessage($message, self::STATUS_DANGER);
            $this->redirect($redirect);            
        }
        return;
    }

    /**
     * append some text to action title
     * @param string $text
     * @return void
     */
    public function appendToTitle(string $text)
    {
        $this->appendTitle = $text;
        return;
    }
}
