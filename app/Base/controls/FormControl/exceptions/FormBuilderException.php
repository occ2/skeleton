<?php
namespace app\Base\controls\FormControl\exceptions;

/**
 * FormBuilderException
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class FormBuilderException extends \Exception
{
    const INVALID_METHOD=1;
    const INVALID_CONFIG_ITEM=2;
}
