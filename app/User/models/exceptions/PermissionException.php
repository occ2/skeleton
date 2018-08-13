<?php
namespace app\User\models\exceptions;

use app\User\models\exceptions\AbstractException as Exception;

/**
 * PermissionException
 * code interval 2300-2399
 * 
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class PermissionException extends Exception
{
    const NOT_LOGGED_IN=2301,
          OPERATION_NOT_PERMITTED=2302,

          MESSAGE_NOT_LOGGED_IN="user.error.permission.notLoggedIn",
          MESSAGE_OPERATION_NOT_PERMITTED="user.error.permission.notPermitted";
}