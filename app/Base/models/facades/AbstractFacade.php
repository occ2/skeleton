<?php
namespace app\Base\models\facades;

use app\Base\events\Event;
use Doctrine\ORM\EntityManager;
use Contributte\EventDispatcher\EventDispatcher;
use Contributte\Utils\DatetimeFactory;
use Nette\DI\Config\Helpers;
use Nette\Security\User;
use Contributte\Cache\ICacheFactory;

/**
 * AbstractFacade
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
abstract class AbstractFacade
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var EventDispatcher
     */
    protected $ed;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var DatetimeFactory
     */
    protected $datetimeFactory;

    /**
     * @var ICacheFactory
     */
    protected $cachingFactory;
    
    /**
     * @var array
     */
    protected $config=[];

    /**
     * @param DatetimeFactory $datetimeFactory
     * @param ICacheFactory $cacheFactory
     * @param EntityManager $em
     * @param EventDispatcher $ed
     * @param User $user
     * @param array $config
     * @return void
     */
    public function __construct(
        DatetimeFactory $datetimeFactory,
        ICacheFactory $cacheFactory,
        EntityManager $em,
        EventDispatcher $ed,
        User $user=null,
        array $config=[])
    {
        $this->em = $em;
        $this->ed = $ed;
        $this->user = $user;
        $this->datetimeFactory = $datetimeFactory;
        $this->cachingFactory = $cacheFactory;
        $this->config = Helpers::merge($config, $this->config);
        return;
    }

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
}