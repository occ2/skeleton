<?php
namespace occ2\GridControl;

/**
 * GridBuilderException
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class GridCallbackException extends \Exception
{
    const UNKNOWN_CALLBACK=1,
          INVALID_CALLBACK=2;
}
