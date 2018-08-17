<?php
namespace app\Base\traits;

use app\Base\factories\MailFactory;
use app\User\models\facades\AdminFacade;

/**
 * TMail
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
trait TMail
{

    /**
     * @var MailFactory
     */
    protected $mailFactory;

    /**
     * mail factor setter
     * @param MailFactory $mailFactory
     * @return $this
     */
    public function setMailFactory(MailFactory $mailFactory)
    {
        $this->mailFactory = $mailFactory;
        return $this;
    }

    /**
     * send email to user
     * @param string $email
     * @param string $name
     * @param string $subject
     * @param string $body
     * @return void
     */
    public function mailUser(string $email,string $name,string $subject,string $body)
    {
        $message = $this->mailFactory->createMessage();
        $message->addTo($email, $name);
        $message->setSubject($subject);
        $message->setBody($body);
        $mailer = $this->mailFactory->createMailer();
        $mailer->send($message);
        return;
    }

    /**
     * send mail to all system admins
     * @param string $subject
     * @param string $body
     * @return void
     */
    public function mailAdmin(AdminFacade $adminFacade, string $subject,string $body)
    {
        $admins = $adminFacade->getAdmins();
        $message = $this->mailFactory->createMessage();
        foreach ($admins as $admin) {
            $message->addTo($admin->getMail());
        }
        $message->setSubject($subject);
        $message->setBody($body);
        $mailer = $this->mailFactory->createMailer();
        $mailer->send($message);
        return;
    }
}