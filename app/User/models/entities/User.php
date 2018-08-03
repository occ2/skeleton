<?php
namespace app\User\models\entities;

use app\Base\traits\TEntityBridge;
use app\User\models\exceptions\ValidationException;
use Doctrine\ORM\Mapping as ORM;
use Nettrine\ORM\Entity\Attributes\Id;
use Contributte\Utils\Validators;
use Nette\Security\Passwords;
use Nette\Utils\DateTime;

/**
 * User
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 * @ORM\Entity
 * @ORM\Table (
 *      name="`Users`",
 *      indexes={
 *         @ORM\Index(name="username_idx", columns={"username"}),
 *         @ORM\Index(name="status_idx", columns={"status"})
 *      }
 * )
 */
class User
{
    use TEntityBridge;
    use Id;

    /**
     * @ORM\OneToMany(targetEntity="Role", mappedBy="user",cascade={"all"})
     */
    private $roles;

    /**
     * @ORM\OneToMany(targetEntity="Settings", mappedBy="user",cascade={"all"})
     */
    private $settings;

    /**
     * @ORM\OneToMany(targetEntity="History", mappedBy="user",cascade={"all"})
     */
    private $history;

    /**
     * @ORM\Column(type="string",unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string")
     */
    private $realname;

    /**
     * @ORM\Column(type="string")
     */
    private $email;

    /**
     * @ORM\Column(type="string")
     */
    private $phone;

    /**
     * @ORM\Column(type="string")
     */
    private $passwordHash;

    /**
     * @ORM\Column(type="datetime")
     */
    private $passwordExpiration;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @ORM\Column(type="integer")
     */
    private $attempts;

    /**
     * @ORM\Column(type="string")
     */
    private $cQuestion;

    /**
     * @ORM\Column(type="string")
     */
    private $cAnswer;

    /**
     * @ORM\Column(type="string")
     */
    private $secret;

    /**
     * @ORM\Column(type="string")
     */
    private $lang;

    public function getRoles()
    {
        return $this->roles;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function getHistory()
    {
        return $this->history;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getRealname()
    {
        return $this->realname;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getPasswordHash()
    {
        return $this->passwordHash;
    }

    /**
     * @return DateTime
     */
    public function getPasswordExpiration()
    {
        return $this->passwordExpiration;
    }

    /**
     * @return bool
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return integer
     */
    public function getAttempts()
    {
        return $this->attempts;
    }

    /**
     * @return string
     */
    public function getCQuestion()
    {
        return $this->cQuestion;
    }

    /**
     * @return string
     */
    public function getCAnswer()
    {
        return $this->cAnswer;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * username setter
     * @param string $username
     * @return $this
     * @throws ValidationException
     */
    public function setUsername(string $username)
    {
        if(!Validators::is($username, "string:4..50")){
            throw new ValidationException("user.error.validation.username",ValidationException::NOT_USERNAME);
        }
        $this->username = $username;
        return $this;
    }

    /**
     * realname setter
     * @param string $realname
     * @return $this
     * @throws ValidationException
     */
    public function setRealname(string $realname)
    {
        if(!Validators::is($realname, "string:4..70")){
            throw new ValidationException("user.error.validation.realname",ValidationException::NOT_REALNAME);
        }
        $this->realname = $realname;
        return $this;
    }

    /**
     * email setter
     * @param string $email
     * @return $this
     * @throws ValidationException
     */
    public function setEmail(string $email)
    {
        if(!Validators::isEmail($email)){
            throw new ValidationException("user.error.validation.email", ValidationException::NOT_EMAIL);
        }
        $this->email = $email;
        return $this;
    }

    /**
     * phone setter
     * @param string $phone
     * @return $this
     * @throws ValidationException
     */
    public function setPhone(string $phone)
    {
        if(!Validators::is($phone, "pattern:\+(?:[0-9]?){6,14}[0-9]")){
            throw new ValidationException("user.error.validation.phone", ValidationException::NOT_PHONE);
        }
        $this->phone = $phone;
        return $this;
    }

    /**
     * password setter
     * @param string $password
     * @return $this
     * @throws ValidationException
     */
    public function setPassword(string $password)
    {
        if(!Validators::is($password,"pattern:.*(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).*")){
            throw new ValidationException("user.error.validation.password",ValidationException::NOT_PASSWORD);
        }
        $this->passwordHash = Passwords::hash($password);
        return $this;
    }

    /**
     * password expiration setter
     * @param string $passwordExpiration
     * @return $this
     */
    public function setPasswordExpiration(string $passwordExpiration=null)
    {
        $datetime = new DateTime;
        if($passwordExpiration!=null){
            $this->passwordExpiration = $datetime->modify($passwordExpiration);
        } else{
            $this->passwordExpiration = $datetime;
        }
        
        return $this;
    }

    /**
     * status setter
     * @param bool $status
     * @return $this
     */
    public function setStatus(bool $status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * attempts setter
     * @param int $attempts
     * @return $this
     */
    public function setAttempts(int $attempts)
    {
        $this->attempts = $attempts;
        return $this;
    }

    /**
     * control question setter
     * @param string $cQuestion
     * @return $this
     * @throws ValidationException
     */
    public function setCQuestion(string $cQuestion)
    {
        if(!Validators::is($cQuestion, "string:4..255")){
            throw new ValidationException("user.error.validation.cQuestion",ValidationException::NOT_QUESTION);
        }
        $this->cQuestion = $cQuestion;
        return $this;
    }

    public function setPasswordHash($passwordHash)
    {
        $this->passwordHash = $passwordHash;
        return $this;
    }

    /**
     * control answer setter
     * @param string $cAnswer
     * @return $this
     * @throws ValidationException
     */
    public function setCAnswer(string $cAnswer)
    {
        if(!Validators::is($cAnswer, "string:4..100")){
            throw new ValidationException("user.error.validation.cAnswer",ValidationException::NOT_ANSWER);
        }
        $this->cAnswer = Passwords::hash($cAnswer);
        return $this;
    }

    /**
     * set secret
     * @param string $secret
     * @return $this
     * @throws ValidationException
     */
    public function setSecret(string $secret)
    {
        if(!Validators::is($secret, "string:8")){
            throw new ValidationException("user.error.validation.secret",ValidationException::NOT_SECRET);
        }
        $this->secret = $secret;
        return $this;
    }

    /**
     * set lang
     * @param string $lang
     * @return $this
     * @throws ValidationException
     */
    public function setLang(string $lang)
    {
        if(!Validators::is($lang, "string:2")){
            throw new ValidationException("user.error.validation.lang",ValidationException::NOT_LANG);
        }
        $this->lang = $lang;
        return $this;
    }

    /**
     * validate password
     * @param string $password
     * @return bool
     */
    public function validatePassword(string $password)
    {
        return Passwords::verify($password, $this->passwordHash);
    }

    /**
     * validate control answer
     * @param string $answer
     * @return bool
     */
    public function validateCAnswer(string $answer)
    {
        return Passwords::verify($answer, $this->cAnswer);
    }

    public function validateAttempts($maxAttempts)
    {
        return $this->attempts < $maxAttempts;
    }

    public function validatePasswordExpiration()
    {
        return $this->passwordExpiration > new DateTime;
    }

    public function validateEmail(string $email)
    {
        return $email==$this->email;
    }

    public function clearAttempts()
    {
        $this->attempts=0;
        return $this;
    }

    public function increaseAttempts()
    {
        $this->attempts++;
        return $this;
    }
}