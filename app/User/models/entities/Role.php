<?php
namespace app\User\models\entities;

use app\Base\traits\TEntityBridge;
use app\User\models\exceptions\ValidationException;
use Doctrine\ORM\Mapping as ORM;
use Nettrine\ORM\Entity\Attributes\Id;
use Contributte\Utils\Validators;

/**
 * Role
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 * @ORM\Entity
 * @ORM\Table (name="`UsersRoles`")
 */
class Role
{
    use TEntityBridge;
    use Id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="roles")
     */
    private $user;

    /**
     * @ORM\Column(type="string")
     */
    private $role;

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * role setter
     * @param string $role
     * @return $this
     */
    public function setRole(string $role)
    {
        if(!Validators::is($role, "string:1..30")){
            throw new ValidationException("user.error.validation.role",ValidationException::NOT_ROLE);
        }
        $this->role = $role;
        return $this;
    }
}