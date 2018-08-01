<?php
namespace app\Base\exceptions;

/**
 * EntityException
 * code interval 1100-1199
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class EntityException extends \Exception
{
    const UNDEFINED_GETTER=1101,
          UNDEFINED_SETTER=1102;
}