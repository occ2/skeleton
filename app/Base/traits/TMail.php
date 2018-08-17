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

namespace app\Base\traits;

use app\Base\factories\MailFactory;
use app\User\models\facades\AdminFacade;

/**
 * TMail
 * mailing helper
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