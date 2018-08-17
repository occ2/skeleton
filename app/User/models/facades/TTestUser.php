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

use Nette\Security\User;
use app\User\models\exceptions\PermissionException;
use app\User\models\entities\User as UserEntity;

/**
 * TTestUser
 *
 * provide current user testing
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
trait TTestUser
{
    /**
     * @var User
     */
    protected $user;

    /**
     * nette security user setter
     * @param User $user
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * test if entity user is same asi logged in user
     * @param UserEntity $user
     * @param bool $throwException
     * @return bool
     * @throws PermissionException
     */
    protected function testUser(UserEntity $user,bool $throwException=true):bool
    {
        if(!$this->user instanceof User || $user->getId()!=$this->user->getId()){
            if($throwException){
                throw new PermissionException(PermissionException::MESSAGE_OPERATION_NOT_PERMITTED, PermissionException::OPERATION_NOT_PERMITTED);
            } else {
                return false;
            }
        }
        return true;
    }
}
