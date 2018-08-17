<?php
namespace app\User\models\exceptions;

use app\User\models\exceptions\AbstractException as Exception;

/**
 * RolesException
 * code interval 2500-2599
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class RolesException extends Exception
{
    const NOT_FOUND=2500,
          ROLE_IS_IN_USE=2501,
          INVALID_ROLE=2502,

          MESSAGE_NOT_FOUND="user.error.role.notFound",
          MESSAGE_ROLE_IN_USE="user.error.role.inUse",
          MESSAGE_INVALID_ROLE="user.error.role.invalid";
    }
