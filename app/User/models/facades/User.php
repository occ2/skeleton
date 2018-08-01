<?php
namespace occ2\inventar\User\models\facades;

use Doctrine\ORM\EntityManager;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\DI\Config\Helpers;
use Nette\Utils\ArrayHash;
use Nette\Utils\Random;
use Nette\Security\Passwords;
use Contributte\EventDispatcher\EventDispatcher;
use occ2\inventar\User\models\exceptions\AuthenticationException;
use occ2\inventar\User\models\events\AuthenticationEvent;
use occ2\inventar\User\models\events\ResetEvent;
use occ2\model\ILogger;
use occ2\inventar\User\controls\forms\ResetPassForm;
use occ2\inventar\User\controls\forms\ControlQuestionForm;
use occ2\inventar\User\models\entities\User as UserEntity;


/**
 * User
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
final class User implements IAuthenticator
{
    const USER_REPOSITORY='\occ2\inventar\User\models\entities\User',
          SETTINGS_REPOSITORY='\occ2\inventar\User\models\entities\Config',
          HISTORY_REPOSITORY='\occ2\inventar\User\models\entities\History',
          ROLES_REPOSITORY='\occ2\inventar\User\models\entities\Role',
        
          EVENT_USER_NOT_FOUND="user.authenticator.notFound",
          EVENT_MAX_ATTEMPTS_REACHED="user.authenticator.attemptsReached",
          EVENT_NOT_APPROVED="user.authenticator.notApproved",
          EVENT_INVALID_CREDENTIAL="user.authenticator.invalidCredential",
          EVENT_EXPIRED_PASSWORD="user.authenticator.expiredPassword",
          EVENT_SUCCESS_LOGIN="user.authenticator.successLogin",
          EVENT_INVALID_EMAIL="user.authenticator.invalidEmail",
          EVENT_INVALID_ANSWER="user.authenticator.invalidAnswer",
          EVENT_SUCCESS_RESET="user.authenticator.successReset";

    /**
     * @var \Nettrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Contributte\EventDispatcher\EventDispatcher
     */
    private $ed;

    /**
     * @var array
     */
    private $config=[
        "maxAttempts"=>5,
        "randomPasswordLength"=>8,
        "passwordExpiration"=>"+90 Days"
    ];

    /**
     * @param EntityManager $em
     * @param EventDispatcher $ed
     * @param array $config
     * @return void
     */
    public function __construct(
        EntityManager $em,
        EventDispatcher $ed,
        array $config=[])
    {
        $this->em = $em;
        $this->ed = $ed;
        $this->config = Helpers::merge($config, $this->config);
        return;
    }

    /**
     * authenticate user
     * @param array $credentials [username,password]
     * @return Identity
     * @throws AuthenticationException
     */
    public function authenticate(array $credentials): Identity
    {
        list($username, $password) = $credentials;
        
        $user = $this->loadUser($username);
        $this->testStatus($user);
        $this->testAttempts($user);
        $this->testPassword($user, $password);

        $identity = new Identity(
            $user->getId(),
            $this->loadRoles($user),
            $this->loadData($user));

        $user->clearAttempts();
        
        $this->ed->dispatch(self::EVENT_SUCCESS_LOGIN, new AuthenticationEvent([$user, ILogger::SUCCESS], self::EVENT_SUCCESS_LOGIN));
        $this->em->flush();
        return $identity;
    }

    /**
     * test reset password form values
     * @param array $values [username,email]
     * @return string control question
     * @throws AuthenticationException
     */
    public function testReset(array $values):string
    {
        $user = $this->loadUser($values[ResetPassForm::USERNAME]);
        $this->testEmail($user, $values[ResetPassForm::EMAIL]);
        return $user->getCQuestion();
    }
  
    public function processReset(array $values, bool $save=false)
    {
        $user = $this->loadUser($values[ResetPassForm::USERNAME]);
        $this->testEmail($user, $values[ResetPassForm::EMAIL]);
        $this->testAnswer($user, $values[ControlQuestionForm::ANSWER]);
        if($save){
            $values[ResetEvent::NEW_PASSWORD] = $this->resetPassword($user);
            $values[ResetEvent::ID] = $user->getId();
            $this->ed->dispatch(self::EVENT_SUCCESS_RESET, new ResetEvent($values));
        } else{
            return true;
        }
    }

    private function resetPassword(UserEntity $user):string
    {
        $password = Random::generate($this->config["randomPasswordLength"]);
        $user->setPasswordHash(Passwords::hash($password))
             ->setPasswordExpiration()
             ->clearAttempts();
        $this->em->flush($user);
        return $password;
    }

    private function testAnswer(UserEntity $user,string $answer)
    {
        if(!$user->validateCAnswer($answer)){
            $this->ed->dispatch(self::EVENT_INVALID_ANSWER, new AuthenticationEvent([$user, ILogger::DANGER], self::EVENT_INVALID_ANSWER));
            throw new AuthenticationException("user.error.authentication.answer", AuthenticationException::INVALID_CONTROL_ANSWER);
        }
    }

    private function testPassword(UserEntity $user,string $password)
    {
        if(!$user->validatePassword($password)){
            $this->ed->dispatch(self::EVENT_INVALID_CREDENTIAL, new AuthenticationEvent([$user, ILogger::DANGER], self::EVENT_INVALID_CREDENTIAL));
            $user->increaseAttempts();
            $this->em->flush();
            throw new AuthenticationException("user.error.authentication.password", AuthenticationException::INVALID_CREDENTIAL);
        }
        if(!$user->validatePasswordExpiration()){
            $this->ed->dispatch(self::EVENT_EXPIRED_PASSWORD, new AuthenticationEvent([$user, ILogger::WARNING], self::EVENT_EXPIRED_PASSWORD));
            throw new AuthenticationException("user.error.authentication.expired", AuthenticationException::PASSWORD_EXPIRED);
        }
        return;
    }

    private function testAttempts(UserEntity $user)
    {
        if(!$user->validateAttempts($this->config["maxAttempts"])){
            $this->ed->dispatch(self::EVENT_MAX_ATTEMPTS_REACHED, new AuthenticationEvent([$user, ILogger::WARNING], self::EVENT_MAX_ATTEMPTS_REACHED));
            throw new AuthenticationException("user.error.authentication.attempts", AuthenticationException::MAX_ATTEMPTS_REACHED);
        }
        return;
    }

    private function testStatus(UserEntity $user)
    {
        if($user->getStatus()==false){
            $this->ed->dispatch(self::EVENT_NOT_APPROVED, new AuthenticationEvent([$user, ILogger::DANGER], self::EVENT_NOT_APPROVED));
            throw new AuthenticationException("user.error.authentication.status", AuthenticationException::NOT_APPROVED);
        }
        return;
    }

    private function testEmail(UserEntity $user,string $email)
    {
        if(!$user->validateEmail($email)){
            $this->ed->dispatch(self::EVENT_INVALID_EMAIL, new AuthenticationEvent([$user, ILogger::DANGER], self::EVENT_INVALID_EMAIL));
            throw new AuthenticationException("user.error.authentication.invalidEmail", AuthenticationException::INVALID_EMAIL);
        }
        return;
    }

    private function loadUser(string $username): UserEntity
    {
        $user = $this->em->getRepository(self::USER_REPOSITORY)->findOneByUsername($username);
        if($user==null){
            $this->ed->dispatch(self::EVENT_USER_NOT_FOUND, new AuthenticationEvent([null, ILogger::DANGER], self::EVENT_USER_NOT_FOUND));
            throw new AuthenticationException("user.error.authentication.identity",AuthenticationException::IDENTITY_NOT_FOUND);
        }
        return $user;
    }

    private function loadData(UserEntity $user): ArrayHash
    {
        $data = $user->toArrayHash();
        $data->settings = $this->loadSettings($user);
        return $data;
    }

    private function loadRoles(UserEntity $user): array
    {
        $r = [];
        $roles = $this->em->getRepository(self::ROLES_REPOSITORY)->findByUser($user->getId());
        foreach ($roles as $role){
            $r[] = $role->getRole();
        }
        return $r;
    }

    private function loadSettings(UserEntity $user): array
    {
        $s = [];
        $settings = $this->em->getRepository(self::SETTINGS_REPOSITORY)->findByUser($user->getId());
        foreach ($settings as $setting) {
            $s[$setting->getKey()] = $setting->getValue();
        }
        return $s;
    }
}