<?php
namespace occ2\inventar\User\models\exceptions;

use occ2\model\ValidationException as BaseException;

/**
 * ValidationException
 *
 * error code interval 2001-2099
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
final class ValidationException extends BaseException
{
    const NOT_USERNAME=2001,
          NOT_REALNAME=2002,
          NOT_PHONE=2003,
          NOT_PASSWORD=2004,
          NOT_SAME_PASSWORD=2005,
          NOT_QUESTION=2006,
          NOT_ANSWER=2007,
          NOT_SECRET=2008,
          NOT_LANG=2009,
          NOT_ROLE=2010;
};