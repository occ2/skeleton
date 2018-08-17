<?php
namespace app\Base\exceptions;

use app\Base\exceptions\AbstractException as Exception;

/**
 * EntityException
 * code interval 1100-1199
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class EntityException extends Exception
{
    const UNDEFINED_GETTER=1101,
          UNDEFINED_SETTER=1102,
          INVALID_GETTER=1103,
          INVALID_SETTER=1104;
}