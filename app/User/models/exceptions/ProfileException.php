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

use app\Base\exceptions\EntityException as BaseException;

/**
 * ProfileException
 * code interval 2200-2299
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class ProfileException extends BaseException
{
    const NOT_FOUND=2216,
          PASSWORDS_NOT_SAME=2200, // exception when passwords on change passwords form not same
          USERNAME_NOT_UNIQUE=2201, // exception if usernane is not unique
          NON_ACCESSABLE_USER=2202, // exception when user's id not be equal with logged user id
          UNAUTHORIZED_USERS_LISTING=2203, // exception when user try to show users list and not have permissions
          UNAUTHORIZED_USER_STATUS_CHANGE=2204,
          UNAUTHORIZED_USER_LOAD=2205,
          UNAUTHORIZED_HISTORY_LOAD=2206,
          UNAUTHORIZED_USER_ADD=2207,
          UNAUTHORIZED_USER_EDIT=2208,
          UNAUTHORIZED_PASSWORD_RESET=2209,
          UNAUTHORIZED_PASSWORD_DELETE=2210,
          UNAUTHORIZED_CONFIG_LOAD=2211,
          UNAUTHORIZED_CONFIG_RESET=2212,
          UNAUTHORIZED_CONFIG_UPDATE=2213,
          UNAUTHORIZED_CONFIG_RELOAD=2214,
          UNAUTHORIZED_USER_DELETE=2215,

          MESSAGE_NOT_FOUND="user.error.user.notFound",
          MESSAGE_NOT_UNIQUE="user.error.username.unique"
    ;
}
