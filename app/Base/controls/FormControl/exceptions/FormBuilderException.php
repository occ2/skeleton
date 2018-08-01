<?php
namespace occ2\FormControl;

/**
 * FormBuilderException
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class FormBuilderException extends \Exception
{
    const INVALID_METHOD=1;
    const INVALID_CONFIG_ITEM=2;
}
