<?php
namespace occ2\inventar\User\models\exceptions;

use Nette\Security\AuthenticationException as NAException;

/**
 * AuthenticationException
 * code interval 2100-2199
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class AuthenticationException extends NAException
{
    const IDENTITY_NOT_FOUND=2000,
          INVALID_CREDENTIAL=2001,
          MAX_ATTEMPTS_REACHED=2002,
          NOT_APPROVED=2003,
          PASSWORD_EXPIRED=2004,
          INVALID_EMAIL=2005,
          INVALID_CONTROL_ANSWER=2006;
}
