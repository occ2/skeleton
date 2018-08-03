<?php
namespace app\User\models\facades;

use app\Base\models\facades\AbstractFacade;
use Nette\Utils\ArrayHash;

/**
 * PasswordFacade
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class PasswordFacade extends AbstractFacade
{
    public function reset(int $id): string
    {}

    public function change(ArrayHash $data)
    {}

    public function expired (ArrayHash $data)
    {}
}