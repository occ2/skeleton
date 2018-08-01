<?php
namespace app\Base\controls\GridControl\exceptions;

/**
 * GridBuilderException
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class GridCallbackException extends \Exception
{
    const UNKNOWN_CALLBACK=1,
          INVALID_CALLBACK=2;
}
