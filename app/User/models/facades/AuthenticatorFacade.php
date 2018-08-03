<?php
namespace app\User\models\facades;

use app\Base\models\facades\AbstractFacade;
use Nette\Security\IAuthenticator;

/**
 * AuthenticatorFacade
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class AuthenticatorFacade extends AbstractFacade implements IAuthenticator
{
    public function authenticate(array $credentials)
    {

    }

    public function verifyReset (string $username, string $email): int
    {
        
    }

    public function verifyAnswer (int $id, string $answer): bool
    {
        
    }
}