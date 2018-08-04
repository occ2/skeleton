<?php
namespace app\User\models\exceptions;

use Exception;

/**
 * PermissionException
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class PermissionException extends Exception
{
    const NOT_LOGGED_IN=1;
    const OPERATION_NOT_PERMITTED=2;
}