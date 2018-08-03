<?php
namespace app\User\models\exceptions;

use Exception;

/**
 * RolesException
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class RolesException extends Exception
{
    const ROLE_IS_IN_USE=1,
          INVALID_ROLE=2;
    }
