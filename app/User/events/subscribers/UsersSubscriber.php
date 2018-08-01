<?php
namespace occ2\inventar\User\models\events;

use Contributte\EventDispatcher\EventSubscriber;
use occ2\inventar\User\models\UsersFacade;
use occ2\inventar\User\models\UsersConfigFacade;
use occ2\inventar\User\models\repositories\Users;
use occ2\inventar\User\models\events\RegisterEvent;
use occ2\model\Event as BaseEvent;
use occ2\inventar\User\models\events\PasswordEvent;
use occ2\inventar\User\models\events\SettingsEvent;
use occ2\inventar\User\models\events\UsersConfigEvent;
use occ2\inventar\factories\MailFactory;
use occ2\inventar\User\models\repositories\UsersHistory;
use occ2\model\ILogger;
use Contributte\Utils\DatetimeFactory;
use Nette\Security\User;
use occ2\inventar\User\models\facades\Profiles;

/**
 * RegisterSubscriber
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
final class UsersSubscriber implements EventSubscriber
{
    
    /**
     * @var \occ2\inventar\factories\MailFactory
     */
    private $mailFactory;
    
    /**
     * @var \occ2\inventar\User\models\repositories\UsersHistory
     */
    private $usersHistoryRepository;

    /**
     * @var \occ2\inventar\User\models\facades\Profiles
     */
    private $usersFacade;

    /**
     * @var \occ2\inventar\User\models\UsersConfigFacade
     */
    private $usersConfigFacade;

    /**
     * @var \Nette\Security\User
     */
    private $user;

    /**
     * @var \Contributte\Utils\DatetimeFactory
     */
    private $datetimeFactory;

    /**
     * @var array
     */
    private $defaultUserConfigs;

    /**
     * @var array
     */
    private $messageMap=[
        UsersFacade::EVENT_REGISTER=>"user.success.register",
        UsersFacade::EVENT_CHANGE_PASSWORD=>"user.success.changePassword",
        UsersFacade::EVENT_SAVE_SETTINGS=>"user.success.saveSettings",
        UsersFacade::EVENT_ADD_USER=>"user.success.addUser",
        UsersFacade::EVENT_EDIT_USER=>"user.success.editUser",
        UsersFacade::EVENT_CHANGE_USER_STATUS=>"user.success.changeStatus",
        UsersFacade::EVENT_RESET_PASSWORD=>"user.success.resetAdmin",
    ];
    
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            UsersFacade::EVENT_REGISTER=>"registerUser",
            UsersFacade::EVENT_CHANGE_PASSWORD=>"changePassword",
            UsersFacade::EVENT_SAVE_SETTINGS=>"saveSettings",
            UsersFacade::EVENT_ADD_USER=>"addUser",
            UsersFacade::EVENT_EDIT_USER=>"editUser",
            UsersFacade::EVENT_CHANGE_USER_STATUS=>"changeStatus",
            UsersFacade::EVENT_DELETE_USER=>"deleteUser",
            UsersFacade::EVENT_RESET_PASSWORD=>"resetUser",
            UsersConfigFacade::EVENT_RESET_CONFIG=>"resetConfig",
            UsersConfigFacade::EVENT_RELOAD_CONFIG=>"reloadConfig"
        ];
    }
    
    /**
     * @param MailFactory $mailFactory
     * @param UsersHistory $usersHistoryRepository
     * @return void
     */
    public function __construct(
        User $user,
        DatetimeFactory $datetimeFactory,
        MailFactory $mailFactory,
        UsersHistory $usersHistoryRepository,
        Profiles $usersFacade,
        UsersConfigFacade $usersConfigFacade,
        $defaultUserConfigs=[]
    )
    {
        $this->mailFactory = $mailFactory;
        $this->usersHistoryRepository = $usersHistoryRepository;
        $this->datetimeFactory = $datetimeFactory;
        $this->usersFacade = $usersFacade;
        $this->usersConfigFacade = $usersConfigFacade;
        $this->defaultUserConfigs = $defaultUserConfigs;
        $this->user = $user;
        return;
    }

    /**
     * inform user about custom configuration reset
     * @param UsersConfigEvent $event
     * @return void
     */
    public function resetConfig(UsersConfigEvent $event)
    {
        $config = $this->usersConfigFacade->searchUserConfig($event->data["user"], UsersConfigFacade::USER_NOTIFY_RESET_CONFIG,false);
        $user = $this->usersFacade->getUser($event->data["user"], true);
        bdump($config);
        if($config==true){
            $this->mailUser(
                $user->{Users::EMAIL},
                $user->{Users::NAME},
                $this->mailFactory->text("user.email.resetConfigSubject"),
                $this->mailFactory->text("user.email.resetConfigMessage")
            );
        }
        return;
    }

    /**
     * inform user about custom configuration reload
     * @param UsersConfigEvent $event
     * @return void
     */
    public function reloadConfig(UsersConfigEvent $event)
    {
        $config = $this->usersConfigFacade->searchUserConfig($event->data["user"], UsersConfigFacade::USER_NOTIFY_RELOAD_CONFIG,false);
        $user = $this->usersFacade->getUser($event->data["user"], true);
        if($config==true){
            $this->mailUser(
                $user->{Users::EMAIL},
                $user->{Users::NAME},
                $this->mailFactory->text("user.email.reloadConfigSubject"),
                $this->mailFactory->text("user.email.reloadConfigMessage")
            );
        }
        return;
    }

    /**
     * default config setter
     * @param array $defaultUserConfigs
     * @return void
     */
    public function setDefaultConfigs($defaultUserConfigs)
    {
        $this->defaultUserConfigs = $defaultUserConfigs;
        return;
    }
    
    
    /**
     * @param PasswordEvent $event
     * @return void
     */
    public function changePassword(PasswordEvent $event)
    {
        $this->log($event);
        $config = $this->usersConfigFacade->searchUserConfig($event->data->{Users::ID},UsersFacade::USER_CONFIG_NOTIFY_PASSWORD,false);
        if($config==true){
            $message = $this->mailFactory->createMessage();
            $message->addTo($event->data[Users::EMAIL]);
            $message->setSubject($this->mailFactory->text("user.email.changePasswordSubject"));
            $message->setBody($this->mailFactory->text("user.email.changePasswordMessage", (array) $event->data));
            $mailer = $this->mailFactory->createMailer();
            $mailer->send($message);            
        }
        return ;
    }
    
    /**
     * @param SettingsEvent $event
     * @return void
     */
    public function saveSettings(SettingsEvent $event)
    {
        return $this->log($event);
    }
    
    /**
     * @param RegisterEvent $event
     * @return void
     */
    public function registerUser(RegisterEvent $event)
    {
        $this->mailUser(
            $event->data->{Users::EMAIL},
            $event->data->{Users::NAME},
            $this->mailFactory->text("user.email.registerSubject"),
            $this->mailFactory->text("user.email.registerMessage", (array)$event->data)
        );
        $this->mailAdmin(
            $this->mailFactory->text("user.email.admin.registerNoticeSubject"),
            $this->mailFactory->text("user.email.admin.registerNoticeMessage", (array)$event->data)
        );
        $this->log($event);
        return;
    }

    /**
     * add user event
     * @param \occ2\inventar\User\models\events\UsersEvent $event
     * @return void
     */
    public function addUser(UsersEvent $event)
    {
        $this->log($event);
        return $this->mailUser(
            $event->data->{Users::EMAIL},
            $event->data->{Users::NAME},
            $this->mailFactory->text("user.email.admin.addSubject"),
            $this->mailFactory->text("user.email.admin.addMessage", (array)$event->data)
        );
    }

    /**
     * edit user event
     * @param \occ2\inventar\User\models\events\UsersEvent $event
     * @return void
     */
    public function editUser(UsersEvent $event)
    {
        $this->log($event);
        $config = $this->usersConfigFacade->searchUserConfig($event->data->{Users::ID},UsersFacade::USER_CONFIG_NOTIFY_EDIT);
        if($config==true){
            return $this->mailUser(
                $event->data->{Users::EMAIL},
                $event->data->{Users::NAME},
                $this->mailFactory->text("user.email.admin.editSubject"),
                $this->mailFactory->text("user.email.admin.editMessage", (array)$event->data)
            );
        } else {
            return;
        }
    }

    /**
     * change status event
     * @param \occ2\inventar\User\models\events\UsersEvent $event
     * @return void
     */
    public function changeStatus(UsersEvent $event)
    {
        $this->log($event);
        $config = $this->usersConfigFacade->searchUserConfig($event->data->{Users::ID},UsersFacade::USER_CONFIG_NOTIFY_CHANGE_STATUS);
        if($config==true){
            $event->data->realStatus = UsersFacade::STATUSES[$event->data->{Users::LANG}][$event->data->{Users::STATUS}];
            return $this->mailUser(
                $event->data->{Users::EMAIL},
                $event->data->{Users::NAME},
                $this->mailFactory->text("user.email.admin.statusSubject"),
                $this->mailFactory->text("user.email.admin.statusMessage", (array)$event->data)
            );
        } else {
            return;
        }
    }

    /**
     * delete user event
     * @param \occ2\inventar\User\models\events\UsersEvent $event
     * @return void
     */
    public function deleteUser(UsersEvent $event)
    {
        return $this->mailUser(
            $event->data->{Users::EMAIL},
            $event->data->{Users::NAME},
            $this->mailFactory->text("user.email.admin.deleteSubject"),
            $this->mailFactory->text("user.email.admin.deleteMessage", (array)$event->data)
        );
    }

    /**
     * reset users password event
     * @param \occ2\inventar\User\models\events\UsersEvent $event
     * @return void
     */
    public function resetUser(UsersEvent $event)
    {
        $this->log($event);
        $config = $this->usersConfigFacade->searchUserConfig($event->data->{Users::ID},UsersFacade::USER_CONFIG_NOTIFY_ADMIN_PASSWORD);
        if($config==true){
            return $this->mailUser(
                $event->data->{Users::EMAIL},
                $event->data->{Users::NAME},
                $this->mailFactory->text("user.email.admin.resetSubject"),
                $this->mailFactory->text("user.email.admin.resetMessage", (array)$event->data)
            );
        } else {
            return;
        }
    }

    /**
     * save log
     * @param BaseEvent $event
     * @return void
     */
    private function log(BaseEvent $event,$type = ILogger::SUCCESS)
    {
        $this->usersHistoryRepository->save([
            UsersHistory::DATETIME=>$this->datetimeFactory->create(),
            UsersHistory::USER=>$event->data[Users::ID],
            UsersHistory::TYPE=> $type,
            UsersHistory::MESSAGE=>$this->messageMap[$event->event]
        ]);
        return;
    }

    /**
     * send email to user
     * @param string $email
     * @param string $name
     * @param string $subject
     * @param string $body
     * @return void
     */
    private function mailUser(string $email,string $name,string $subject,string $body)
    {
        $message = $this->mailFactory->createMessage();
        $message->addTo($email, $name);
        $message->setSubject($subject);
        $message->setBody($body);
        $mailer = $this->mailFactory->createMailer();
        return $mailer->send($message);
    }

    /**
     * send mail to all system admins
     * @param string $subject
     * @param string $body
     * @return void
     */
    private function mailAdmin(string $subject,string $body)
    {
        $admins = $this->usersFacade->getAdmins();
        $message = $this->mailFactory->createMessage();
        foreach ($admins as $admin) {
            $message->addTo($admin->{Users::EMAIL});
        }
        $message->setSubject($subject);
        $message->setBody($body);
        $mailer = $this->mailFactory->createMailer();
        return $mailer->send($message);
    }
}
