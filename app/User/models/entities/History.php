<?php
namespace app\User\models\entities;

use app\Base\traits\TEntityBridge;
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
class History
{
    use TEntityBridge;
    use Id;

    /**
     * @ORM\ManyToOne(targetEntity="User",inversedBy="history")
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $datetime;

    /**
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @ORM\Column(type="string")
     */
    private $message;

    /**
     * @ORM\Column(type="array")
     */
    private $data=[];

    public function getUser()
    {
        return $this->user;
    }

    public function getDatetime()
    {
        return $this->datetime;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    public function setType(int $type)
    {
        $this->type = $type;
        return $this;
    }

    public function setMessage(string $message)
    {
        $this->message = $message;
        return $this;
    }

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