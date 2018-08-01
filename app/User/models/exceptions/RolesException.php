<?php
namespace occ2\inventar\User\models\exceptions;

use Nette\Security\AuthenticationException as NAException;

/**
 * RolesException
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class RolesException extends NAException
{
    const ROLE_IS_IN_USE=1,
          INVALID_ROLE=2;
    }
