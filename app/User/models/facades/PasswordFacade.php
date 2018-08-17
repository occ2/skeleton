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
use app\User\models\exceptions\ValidationException;
use app\User\models\exceptions\ProfileException;
use app\User\events\data\PasswordEvent;
use app\User\models\facades\TTestUser;
use Nette\Security\Passwords;
use Nette\Utils\Random;

/**
 * PasswordFacade
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class PasswordFacade extends AbstractFacade
{
    use TTestUser;

    const EVENT_RESET="password.onReset",
          EVENT_EXPIRED="password.onExpiredChange",
          EVENT_CHANGE="password.onChange",
          ENTITY_CLASS=UserEntity::class;

    /**
     * @var array
     */
    protected $config=[
        "randomPasswordLength"=>8,
        "passwordExpiration"=>"+90 Days"
    ];

    /**
     * reset user password by random
     * @param int $id
     * @return string
     */
    public function reset(int $id): string
    {
        $password = Random::generate($this->config["randomPasswordLength"]);
        $user = $this->em->find(UserEntity::class, $id);
        $this->testFound($user, ProfileException::class);
        if($user!=null){
            $this->save($user, $password);
        }
        $this->on(
            self::EVENT_RESET,
            new PasswordEvent(
                [
                    PasswordEvent::ENTITY=>$user,
                    PasswordEvent::PASSWORD=>$password
                ],
                self::EVENT_RESET
            )
        );
        return $password;
    }

    /**
     * change password
     * @param int $id
     * @param string $oldPassword
     * @param string $newPassword
     * @param string $repeatedPassword
     * @return void
     */
    public function change(int $id, string $oldPassword, string $newPassword, string $repeatedPassword)
    {
        $user = $this->em->find(UserEntity::class, $id);
        $this->testFound($user, ProfileException::class);
        if($user!=null){
            $this->testUser($user);
            $this->testPassword($user, $oldPassword);
            $this->compare($newPassword, $repeatedPassword);
            $this->save($user, $newPassword);
            $this->on(
                self::EVENT_CHANGE,
                new PasswordEvent(
                    [
                        PasswordEvent::ENTITY=>$user,
                        PasswordEvent::PASSWORD=>$newPassword
                    ],
                    self::EVENT_CHANGE
                )
            );
        }
        return;
    }

    /**
     * change expired password
     * @param string $username
     * @param string $oldPassword
     * @param string $newPassword
     * @param string $repeatedPassword
     * @return void
     */
    public function expired(string $username, string $oldPassword, string $newPassword, string $repeatedPassword)
    {
        $user = $this->loadUser($username);
        if($user!=null){
            $this->testPassword($user, $oldPassword);
            $this->compare($newPassword, $repeatedPassword);
            $this->save($user, $newPassword);
            $this->on(
                self::EVENT_EXPIRED,
                new PasswordEvent(
                    [
                        PasswordEvent::ENTITY=>$user,
                        PasswordEvent::PASSWORD=>$newPassword
                    ],
                    self::EVENT_EXPIRED
                )
            );
        }
        return;
    }

    /**
     * change user password
     * @param UserEntity $user
     * @param string $password
     * @return void
     */
    private function save(UserEntity $user, string $password)
    {
        $datetime = $this->datetimeFactory->create();
        $user->setPasswordHash(Passwords::hash($password))
             ->setPasswordExpiration($datetime->modify($this->config["passwordExpiration"]))
             ->clearAttempts();
        $this->em->flush($user);
        return;
    }

    /**
     * compare new and repeated password
     * @param string $first
     * @param string $second
     * @throws ValidationException
     */
    private function compare(string $first,string $second)
    {
        if($first!==$second){
            throw new ValidationException(ValidationException::MESSAGE_NOT_EQUAL,ValidationException::NOT_SAME_PASSWORD);
        }
        return;
    }
}