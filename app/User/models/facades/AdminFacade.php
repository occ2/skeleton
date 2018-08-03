<?php
namespace app\User\models\facades;

use app\Base\models\facades\BaseFacade;
use app\User\models\entities\User;
use Doctrine\Common\Collections\Collection;
use Nette\Utils\ArrayHash;

/**
 * AdminFacade
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class AdminFacade extends BaseFacade
{
    public function load(): Collection
    {

    }

    public function find(int $id): User
    {

    }

    public function save(ArrayHash $data)
    {

    }

    public function remove(int $id)
    {

    }

    public function changeStatus(int $id,int $status)
    {

    }
}