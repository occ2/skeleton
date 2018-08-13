<?php
namespace app\User\models\exceptions;

use Nette\Security\AuthenticationException as NAException;

/**
 * AuthenticationException
 * code interval 2100-2199
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class AuthenticationException extends NAException
{
    const IDENTITY_NOT_FOUND=2100,
          INVALID_CREDENTIAL=2101,
          MAX_ATTEMPTS_REACHED=2102,
          NOT_APPROVED=2103,
          PASSWORD_EXPIRED=2104,
          INVALID_EMAIL=2105,
          INVALID_CONTROL_ANSWER=2106,

          MESSAGE_NOT_APPROVED="user.error.authentication.status",
          MESSAGE_MAX_ATTEMPTS_REACHED="user.error.authentication.attempts",
          MESSAGE_PASSWORD_EXPIRED="user.error.authentication.expired",
          MESSAGE_INVALID_EMAIL="user.error.email.invalid",
          MESSAGE_INVALID_CONTROL_ANSWER="user.error.answer.invalid",
          MESSAGE_IDENTITY_NOT_FOUND="user.error.authentication.identity",
          MESSAGE_INVALID_CREDENTIAL="user.error.authentication.password";
}
