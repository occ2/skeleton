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

namespace app\User\models\entities;

use app\Base\models\entities\IEntity;
use app\Base\traits\TEntityBridge;
use app\User\models\entities\User as UserEntity;
use Doctrine\ORM\Mapping as ORM;
use Nettrine\ORM\Entity\Attributes\Id;
use Nette\Utils\DateTime;

/**
 * History
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 * @ORM\Entity
 * @ORM\Table (
 *  name="`UsersHistory`",
 *  indexes={
 *         @ORM\Index(name="datetime_idx", columns={"datetime"})
 *          }
 *  )
 * @ORM\HasLifecycleCallbacks
 */
class History implements IEntity
{
    use TEntityBridge;
    use Id;

    const ID="id",
          USER="user",
          DATETIME="datetime",
          TYPE="type",
          MESSAGE="message",
          DATA="data";


    /**
     * @ORM\ManyToOne(targetEntity="User",inversedBy="history")
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $datetime;

    /**
     * @ORM\Column(type="string")
     */
    private $type;

    /**
     * @ORM\Column(type="string")
     */
    private $message;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $data=[];

    /**
     * @return UserEntity
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return DateTime
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param UserEntity $user
     * @return $this
     */
    public function setUser(UserEntity $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function onSave()
    {
        $this->datetime = new DateTime();
    }
}