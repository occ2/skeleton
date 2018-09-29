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

use app\Base\models\facades\BaseFacade;
use app\User\models\entities\User as UserEntity;
use app\User\models\entities\History as HistoryEntity;
use Doctrine\Common\Collections\Collection;
use app\Base\models\interfaces\ILogger;
use app\User\models\exceptions\ProfileException;

/**
 * HistoryFacade
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class HistoryFacade extends BaseFacade
{
    const DEFAULT_TYPE=ILogger::INFO;

    /**
     * save history item
     * @param UserEntity | int $user
     * @param string $message
     * @param string $type
     * @param array $data
     * @return void
     */
    public function save(
        $user,
        string $message,
        string $type=self::DEFAULT_TYPE,
        array $data=[]

    )
    {
        if(!$user instanceof UserEntity){
            $u = $this->em->find(UserEntity::class,$user);
            if($u==null){
                throw new ProfileException(ProfileException::MESSAGE_NOT_FOUND, ProfileException::NOT_FOUND);
            }
        } else {
            $u = $user;
        }
        $item = new HistoryEntity;
        $item->setMessage($message)
             ->setType($type)
             ->setUser($u)
             ->setData($data);
        $this->em->persist($item);
        $this->em->flush();
        return;
    }

    /**
     * load users history
     * @param int | UserEntity $userId
     * @return ?Collection
     */
    public function load($user): ?Collection
    {
        if($user instanceof UserEntity){
            return $user->getHistory();
        } else{
            $user = $this->em->find(UserEntity::class, $user);
            return $user==null ? null : $user->getHistory();
        }
    }
}