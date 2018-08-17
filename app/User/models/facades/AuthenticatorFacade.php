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

namespace app\User\models\facades;

use app\User\models\facades\BaseFacade as AbstractFacade;
use app\User\models\entities\User as UserEntity;
use app\User\models\exceptions\AuthenticationException;
use app\User\events\data\AuthenticationEvent;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\Utils\ArrayHash;
use Nette\Utils\Random;
use Nette\Security\Passwords;

/**
 * AuthenticatorFacade
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class AuthenticatorFacade extends AbstractFacade implements IAuthenticator
{
    const EVENT_MAX_ATTEMPTS_REACHED="authenticator.onMaxAttemptsReached",
          EVENT_NOT_APPROVED="authenticator.onNotApproved",
          EVENT_EXPIRED_PASSWORD="authenticator.onExpiredPassword",
          EVENT_SUCCESS_LOGIN="authenticator.onSuccessLogin",
          EVENT_INVALID_EMAIL="authenticator.onInvalidEmail",
          EVENT_INVALID_ANSWER="authenticator.onInvalidAnswer",
          EVENT_SUCCESS_RESET="authenticator.onSuccessReset";

    /**
     * @var array
     */
    protected $config=[
        "maxAttempts"=>5,
        "randomPasswordLength"=>8,
        "passwordExpiration"=>"+90 Days"
    ];

    /**
     * authenticate user
     * @param array $credentials
     * @return Identity | false
     */
    public function authenticate(array $credentials)
    {
        list($username, $password) = $credentials;

        $user = $this->loadUser($username);
        $this->testStatus($user);
        $this->testAttempts($user);
        $this->testPassword($user, $password);

        if($user instanceof UserEntity){
            $identity = new Identity(
                $user->getId(),
                $this->loadRoles($user),
                $this->loadData($user));

            $user->clearAttempts();

            $this->on(
                self::EVENT_SUCCESS_LOGIN,
                new AuthenticationEvent(
                    [
                        AuthenticationEvent::ENTITY=>$user
                    ],
                    self::EVENT_SUCCESS_LOGIN
                )
            );
            $this->em->flush();
            return $identity;
        } else {
            return false;
        }
    }

    /**
     * verify reset credentials
     * @param string $username
     * @param string $email
     * @return UserEntity | null
     */
    public function verifyReset (string $username, string $email): ?UserEntity
    {
        $user = $this->loadUser($username);
        $this->testEmail($user, $email);
        return $user;
    }

    /**
     * verify control answer and reset password
     * @param int $id
     * @param string $answer
     * @return void
     */
    public function verifyAnswerAndResetPassword (int $id, string $answer)
    {
        $user = $this->em->find(UserEntity::class,$id);
        $this->testAnswer($user, $answer);
        if($user!=null){
            $values=[
                UserEntity::ID=>$id,
                UserEntity::PASSWORD=>$this->resetPassword($user),
                UserEntity::EMAIL=>$user->getEmail(),
                UserEntity::USERNAME=>$user->getUsername(),
                UserEntity::REALNAME=>$user->getRealname()
            ];

            $this->on(
                self::EVENT_SUCCESS_RESET,
                new AuthenticationEvent(
                    [
                        AuthenticationEvent::VALUES=>$values
                    ],
                    self::EVENT_SUCCESS_RESET
                )
            );
        }
        return;
    }

    /**
     * test user status
     * @param UserEntity | null $user
     * @return void
     * @throws AuthenticationException
     */
    private function testStatus(?UserEntity $user)
    {
        if($user instanceof UserEntity && $user->getStatus()==false){
            $this->on(
                self::EVENT_NOT_APPROVED,
                new AuthenticationEvent(
                    [
                        AuthenticationEvent::ENTITY=>$user
                    ],
                    self::EVENT_NOT_APPROVED
                )
            );
            throw new AuthenticationException(AuthenticationException::MESSAGE_NOT_APPROVED, AuthenticationException::NOT_APPROVED);
        }
        return;
    }

    /**
     * test maximum attemtps reached
     * @param UserEntity | null $user
     * @return void
     * @throws AuthenticationException
     */
    private function testAttempts(?UserEntity $user)
    {
        if($user instanceof UserEntity && !$user->validateAttempts($this->config["maxAttempts"])){
            $this->on(
                self::EVENT_MAX_ATTEMPTS_REACHED,
                new AuthenticationEvent(
                    [
                        AuthenticationEvent::ENTITY=>$user
                    ],
                    self::EVENT_MAX_ATTEMPTS_REACHED
                )
            );
            throw new AuthenticationException(AuthenticationException::MESSAGE_MAX_ATTEMPTS_REACHED, AuthenticationException::MAX_ATTEMPTS_REACHED);
        }
        return;
    }

    /**
     * test password is valid
     * @param UserEntity | null $user
     * @param string $password
     * @return void
     * @throws AuthenticationException
     */
    protected function testPassword(?UserEntity $user,string $password)
    {
        parent::testPassword($user, $password);
        if($user instanceof UserEntity && !$user->validatePasswordExpiration()){
            $this->on(
                self::EVENT_EXPIRED_PASSWORD,
                new AuthenticationEvent(
                    [
                        AuthenticationEvent::ENTITY=>$user
                    ],
                    self::EVENT_EXPIRED_PASSWORD
                )
            );
            throw new AuthenticationException(AuthenticationException::MESSAGE_PASSWORD_EXPIRED, AuthenticationException::PASSWORD_EXPIRED);
        }
        return;
    }

    /**
     * test if email is valid
     * @param UserEntity | null $user
     * @param string $email
     * @return void
     * @throws AuthenticationException
     */
    private function testEmail(?UserEntity $user,string $email)
    {
        if($user instanceof UserEntity && !$user->validateEmail($email)){
            $this->on(
                self::EVENT_INVALID_EMAIL,
                new AuthenticationEvent(
                    [
                        AuthenticationEvent::ENTITY=>$user
                    ],
                    self::EVENT_INVALID_EMAIL
                )
            );
            throw new AuthenticationException(AuthenticationException::MESSAGE_INVALID_EMAIL, AuthenticationException::INVALID_EMAIL);
        }
        return;
    }

    /**
     * test control answer
     * @param UserEntity | null $user
     * @param string $answer
     * @return void
     * @throws AuthenticationException
     */
    private function testAnswer(?UserEntity $user,string $answer)
    {
        if($user instanceof UserEntity && !$user->validateCAnswer($answer)){
            $this->on(
                self::EVENT_INVALID_ANSWER,
                new AuthenticationEvent(
                    [
                        AuthenticationEvent::ENTITY=>$user
                    ],
                    self::EVENT_INVALID_ANSWER
                )
            );
            throw new AuthenticationException(AuthenticationException::MESSAGE_INVALID_CONTROL_ANSWER, AuthenticationException::INVALID_CONTROL_ANSWER);
        }
        return;
    }

    /**
     * load and complete users data
     * @param UserEntity $user
     * @return ArrayHash
     */
    private function loadData(UserEntity $user): ArrayHash
    {
        $data = $user->toArrayHash();
        $data->settings = $this->loadSettings($user);
        return $data;
    }

    /**
     * load users roles
     * @param UserEntity $user
     * @return array
     */
    private function loadRoles(UserEntity $user): array
    {
        $r = [];
        $roles = $user->getRoles();
        foreach ($roles as $role){
            $r[] = $role->getRole();
        }
        return $r;
    }

    /**
     * load users settings
     * @param UserEntity $user
     * @return array
     */
    private function loadSettings(UserEntity $user): array
    {
        $s = [];
        $settings = $user->getSettings();
        foreach ($settings as $setting) {
            $s[$setting->getKey()] = $setting->getValue();
        }
        return $s;
    }

    /**
     * reset user password
     * @param UserEntity| null  $user
     * @return string | null
     */
    private function resetPassword(?UserEntity $user): ?string
    {
        if($user!=null){
            $password = Random::generate($this->config["randomPasswordLength"]);
            $user->setPasswordHash(Passwords::hash($password))
                 ->setPasswordExpiration()
                 ->clearAttempts();
            $this->em->flush($user);
            return $password;
        } else {
            return null;
        }
    }
}