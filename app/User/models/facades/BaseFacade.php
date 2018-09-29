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

use app\Base\models\facades\BaseFacade as Facade;
use app\Base\models\interfaces\ILogger;
use app\User\events\data\AuthenticationEvent;
use app\User\events\data\ProfileEvent;
use app\User\models\entities\User as UserEntity;
use app\User\models\exceptions\AuthenticationException;
use app\User\models\exceptions\ProfileException;

/**
 * BaseFacade
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
abstract class BaseFacade extends Facade
{
    const EVENT_USER_NOT_FOUND="authenticator.onUserNotFound",
          EVENT_INVALID_CREDENTIAL="authenticator.onInvalidCredential",
          EVENT_FIND="User.AdminFacade.onFind";

    /**
     * load user by username
     * @param string $username
     * @param bool $exception
     * @return UserEntity | null
     * @throws AuthenticationException
     */
    protected function loadUser(string $username,$exception=true): ?UserEntity
    {
        $user = $this->em->getRepository(UserEntity::class)->findOneBy([UserEntity::USERNAME=>$username]);
        if($user==null){
            $this->on(self::EVENT_USER_NOT_FOUND, new AuthenticationEvent([null, ILogger::ERROR], self::EVENT_USER_NOT_FOUND));
            if($exception){
                throw new AuthenticationException(AuthenticationException::MESSAGE_IDENTITY_NOT_FOUND,AuthenticationException::IDENTITY_NOT_FOUND);
            } else {
                return null;
            }
        }
        return $user;
    }

    /**
     * find user by id
     * @param int $id
     * @param bool $fireEvent
     * @param bool $throwException
     * @return UserEntity | null
     */
    public function find(int $id,bool $fireEvent=true,bool $throwException=true): ?UserEntity
    {
        $exceptionClass = $throwException==true ? ProfileException::class : null;
        $user = $this->em->find(UserEntity::class, $id);
        $this->testFound($user, $exceptionClass);
        if($fireEvent){
            $this->on(
                static::EVENT_FIND,
                new ProfileEvent(
                    [
                        ProfileEvent::ENTITY=>$user
                    ],
                    static::EVENT_FIND
                )
            );
        }
        return $user;
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
        if($user instanceof UserEntity && !$user->validatePassword($password)){
            $this->on(self::EVENT_INVALID_CREDENTIAL, new AuthenticationEvent([$user, ILogger::ERROR], self::EVENT_INVALID_CREDENTIAL));
            $user->increaseAttempts();
            $this->em->flush();
            throw new AuthenticationException(AuthenticationException::MESSAGE_INVALID_CREDENTIAL, AuthenticationException::INVALID_CREDENTIAL);
        }
        return;
    }
}