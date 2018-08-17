<?php
namespace app\Base\exceptions;

use app\Base\exceptions\AbstractException as Exception;

/**
 * ValidationException
 * code interval 1001-1099
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 *
 */
abstract class ValidationException extends Exception
{
    const NOT_NUMBER=1001,
          NOT_NUMERIC=1002,
          NOT_INTEGER=1003,
          NOT_CALLBACK=1004,
          NOT_UNICODE=1005,
          NOT_LIST=1006,
          NOT_IN_RANGE=1007,
          NOT_EMAIL=1008,
          NOT_URL=1009,
          NOT_URI=1010,
          NOT_ICO=1011,
          NOT_RC=1012;
}