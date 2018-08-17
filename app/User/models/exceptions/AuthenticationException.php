<?php
/*
 * The MIT License
 *
 * Copyright 2018 Milan Onderka <milan_onderka@occ2.cz>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

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
