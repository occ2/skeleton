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

/**
 * Config
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 * @ORM\Entity
 * @ORM\Table (
 *  name="`UsersSettings`",
 *  indexes={
 *         @ORM\Index(name="xkey_idx", columns={"xkey"})
 *          }
 *  )
 */
class Settings implements IEntity
{
    use TEntityBridge;
    use Id;

    const ID="id",
          USER="user",
          KEY="xkey",
          VALUE="xvalue",
          COMMENT="xcomment",
          TYPE="xtype";

    /**
     * @ORM\ManyToOne(targetEntity="User",inversedBy="settings")
     */
    private $user;

    /**
     * @ORM\Column(type="string")
     */
    private $xkey;

    /**
     * @ORM\Column(type="string")
     */
    private $xvalue;

    /**
     * @ORM\Column(type="string")
     */
    private $xcomment;

    /**
     * @ORM\Column(type="string")
     */
    private $xtype;

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->xkey;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->xvalue;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->xcomment;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->xtype;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setKey(string $key)
    {
        $this->xkey = $key;
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setValue(string $value)
    {
        $this->xvalue = $value;
        return $this;
    }

    /**
     * @param string $comment
     * @return $this
     */
    public function setComment(string $comment)
    {
        $this->xcomment = $comment;
        return $this;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type)
    {
        $this->xtype = $type;
        return $this;
    }

    /**
     * @return UserEntity
     */
    public function getUser()
    {
        return $this->user;
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
     * alias for getKey
     * @return string
     */
    public function getXkey()
    {
        return $this->getKey();
    }

    /**
     * alias for setKey
     * @param string $key
     * @return $this
     */
    public function setXkey(string $key)
    {
        return $this->setKey($key);
    }

    /**
     * alias for getValue
     * @return string
     */
    public function getXvalue()
    {
        return $this->getValue();
    }

    /**
     * alias for setValue
     * @param string $value
     * @return $this
     */
    public function setXvalue(string $value)
    {
        return $this->setValue($value);
    }

    /**
     * alias for getComment
     * @return string
     */
    public function getXcomment()
    {
        return $this->getComment();
    }

    /**
     * alias for setComment
     * @param string $comment
     * @return $this
     */
    public function setXcomment(string $comment)
    {
        return $this->setComment($comment);
    }

    /**
     * alias for getType
     * @return string
     */
    public function getXtype()
    {
        return $this->getType();
    }

    /**
     * alias for getType
     * @param string $type
     * @return $this
     */
    public function setXtype(string $type)
    {
        return $this->setType($type);
    }
}