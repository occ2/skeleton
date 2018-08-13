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
    const ROLE_IS_IN_USE=2501,
          INVALID_ROLE=2502;
    }
