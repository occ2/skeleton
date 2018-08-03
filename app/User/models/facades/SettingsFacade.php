<?php
namespace app\User\models\facades;

use app\Base\models\facades\BaseFacade;
use app\User\models\entities\Settings;
use Doctrine\Common\Collections\Collection;
use Nette\Utils\ArrayHash;

/**
 * SettingsFacade
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class SettingsFacade extends BaseFacade
{
    public function find(int $id): Settings
    {

    }

    public function load(int $userId): Collection
    {

    }

    public function save(ArrayHash $data)
    {

    }

    public function reset(int $userId)
    {

    }

    public function reload(int $userId)
    {

    }
}