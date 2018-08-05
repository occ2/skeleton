<?php
namespace app\Base\models\facades;

use Doctrine\ORM\EntityManager;
use Contributte\EventDispatcher\EventDispatcher;
use Contributte\Utils\DatetimeFactory;
use Nette\DI\Config\Helpers;
use Nette\Security\User;
use Nette\Caching\IStorage;
use Nette\Reflection\AnnotationsParser;

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
     * @var IStorage
     */
    protected $cachingStorage;

    /**
     * @var DatetimeFactory
     */
    protected $datetimeFactory;
    
    /**
     * @var array
     */
    protected $config=[];

    /**
     * @param DatetimeFactory $datetimeFactory
     * @param EntityManager $em
     * @param EventDispatcher $ed
     * @param User $user
     * @param array $config
     * @return void
     */
    public function __construct(
        DatetimeFactory $datetimeFactory,
        EntityManager $em,
        EventDispatcher $ed,
        User $user=null,
        IStorage $cachingStorage=null,
        array $config=[])
    {
        $this->em = $em;
        $this->ed = $ed;
        $this->user = $user;
        $this->datetimeFactory = $datetimeFactory;
        $this->cachingStorage = $cachingStorage;
        $this->config = Helpers::merge($config, $this->config);
        return;
    }
}