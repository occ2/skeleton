<?php
namespace app\Base\models\facades;

use Doctrine\ORM\EntityManager;
use Contributte\EventDispatcher\EventDispatcher;
use Contributte\Utils\DatetimeFactory;
use Nette\DI\Config\Helpers;
use Nette\Security\User;

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
        User $user,
        array $config=[])
    {
        $this->em = $em;
        $this->ed = $ed;
        $this->user = $user;
        $this->datetimeFactory = $datetimeFactory;
        $this->config = Helpers::merge($config, $this->config);
        return;
    }
}