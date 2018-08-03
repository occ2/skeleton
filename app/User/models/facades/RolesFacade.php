<?php
namespace app\User\models\facades;

use app\Base\models\facades\BaseFacade;
use app\User\models\entities\Role;
use Doctrine\Common\Collections\Collection;
use Nette\Utils\ArrayHash;

/**
 * RolesFacade
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class RolesFacade extends BaseFacade
{
    public function load(int $userId): Collection
    {

    }

    public function find(int $id): Role
    {

    }

    public function add(ArrayHash $data)
    {

    }

    public function remove(int $id)
    {
        
    }
}