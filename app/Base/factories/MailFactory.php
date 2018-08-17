<?php
namespace app\Base\factories;

use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Nette\Mail\SmtpMailer;
use Kdyby\Translation\ITranslator;

/**
 * mail factory
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class MailFactory
{
    
    /**
     * @var \Nette\Localization\ITranslator
     */
    public $translator;
    
    /**
     * @var array
     */
    public $config;
    
    /**
     * @param array $config
     * @return void
     */
    public function __construct(ITranslator $translator, $config)
    {
        $this->config = $config;
        $this->translator = $translator;
        return;
    }
    
    /**
     * create message
     * @return \Nette\Mail\Message
     */
    public function createMessage()
    {
        $m = new Message;
        if (isset($this->config["fromAddr"])) {
            if (isset($this->config["fromName"])) {
                $m->setFrom($this->config["fromAddr"], $this->config["fromName"]);
            } else {
                $m->setFrom($this->config["fromAddr"]);
            }
        }
        if (isset($this->config["replyAddr"])) {
            if (isset($this->config["replyName"])) {
                $m->addReplyTo($this->config["replyAddr"], $this->config["replyName"]);
            } else {
                $m->addReplyTo($this->config["replyAddr"]);
            }
        }
        if (isset($this->config["returnPath"])) {
            $m->setReturnPath($this->config["returnPath"]);
        }
        if (isset($this->config["priority"])) {
            $m->setPriority($this->config["priority"]);
        }
        return $m;
    }
    
    /**
     * create mailer
     * @return \Nette\Mail\SendmailMailer|\Nette\Mail\SmtpMailer
     */
    public function createMailer()
    {
        if (!isset($this->config["type"]) or $this->config["type"]!="smtp") {
            return new SendmailMailer();
        } else {
            return new SmtpMailer($this->config);
        }
    }
    
    /**
     * translate text for mail
     * @param string $message
     * @param int $count
     * @param array $parameters
     * @return string
     * @deprecated
     */
    public function text(string $message, $count=null, $parameters=[])
    {
        if ($this->translator instanceof ITranslator) {
            return $this->translator->translate($message, $count, $parameters);
        } else {
            return $message;
        }
    }

    /**
     * translate text for mail
     * @param string $message
     * @param int $count
     * @param array $parameters
     * @return string
     * @deprecated
     */
    public function _(string $message, $count=null, $parameters=[])
    {
        if ($this->translator instanceof ITranslator) {
            return $this->translator->translate($message, $count, $parameters);
        } else {
            return $message;
        }
    }
}
