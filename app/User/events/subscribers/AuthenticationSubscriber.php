<?php
namespace occ2\inventar\User\models\events;

use Contributte\EventDispatcher\EventSubscriber;
use occ2\inventar\User\models\events\AuthenticationEvent;
use occ2\inventar\User\models\events\ResetEvent;
use occ2\inventar\User\models\repositories\UsersHistory;
use occ2\inventar\factories\MailFactory;
use occ2\inventar\User\models\entities\History;
use occ2\model\ILogger;
use occ2\inventar\User\models\facades\User;
use Nettrine\ORM\EntityManager;

/**
 * AuthenticationSubscriber
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
final class AuthenticationSubscriber implements EventSubscriber
{   
    /**
     * @var array
     */
    protected $messageMap=[
        User::EVENT_EXPIRED_PASSWORD=>"user.error.expiredPassword",
        User::EVENT_INVALID_CREDENTIAL=>"user.error.invalidPassword",
        User::EVENT_MAX_ATTEMPTS_REACHED=>"user.error.maxAttemptsReached",
        User::EVENT_NOT_APPROVED=>"user.error.blockedUser",
        User::EVENT_SUCCESS_LOGIN=>"user.success.login",
        User::EVENT_USER_NOT_FOUND=>"user.error.invalidUsername",
        User::EVENT_SUCCESS_RESET=>"user.success.reset",
        User::EVENT_INVALID_EMAIL=>"user.error.invalidEmail",
        User::EVENT_INVALID_ANSWER=>"user.error.invalidAnswer"
    ];
    
    /**
     * @var \occ2\inventar\factories\MailFactory
     */
    private $mailFactory;

    /**
     * @var \Nettrine\ORM\EntityManager
     */
    private $em;
    
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            User::EVENT_EXPIRED_PASSWORD=>"log",
            User::EVENT_INVALID_CREDENTIAL=>"log",
            User::EVENT_MAX_ATTEMPTS_REACHED=>"log",
            User::EVENT_NOT_APPROVED=>"log",
            User::EVENT_SUCCESS_LOGIN=>"log",
            User::EVENT_USER_NOT_FOUND=>"log",
            User::EVENT_INVALID_EMAIL=>"log",
            User::EVENT_INVALID_ANSWER=>"log",
            User::EVENT_SUCCESS_RESET=>"mailReset"
        ];
    }
    
    /**
     * @param UsersHistory $usersHistoryRepository
     * @return void
     */
    public function __construct(
        EntityManager $em,
        MailFactory $mailFactory
    )
    {
        $this->mailFactory = $mailFactory;
        $this->em = $em;
        return;
    }
    
    /**
     * log event
     * @param AuthenticationEvent $event
     * @return void
     */
    public function log(AuthenticationEvent $event)
    {
        $history = new History;
        $history->setUser($event->data[0])
                ->setType($event->data[1])
                ->setMessage($this->messageMap[$event->event]);
        $this->em->persist($history);
        $this->em->flush();
        return;
    }
    
    /**
     * mail new reseted password
     * @param ResetEvent $event
     * @return type
     */
    public function mailReset(ResetEvent $event)
    {
        $user = $this->em->find(User::USER_REPOSITORY, $event->data[ResetEvent::ID]);
        $message = $this->mailFactory->createMessage();
        $message->addTo($event->data[ResetEvent::EMAIL]);
        $message->setSubject($this->mailFactory->text("user.email.resetSubject"));
        $message->setBody($this->mailFactory->text("user.email.resetMessage", $event->data));
        $mailer = $this->mailFactory->createMailer();

        $history = new History;
        $history->setUser($user)
                ->setType(ILogger::SUCCESS)
                ->setMessage($this->messageMap[User::EVENT_SUCCESS_RESET]);
        $this->em->persist($history);
        $this->em->flush();
        
        return $mailer->send($message);
    }
}
