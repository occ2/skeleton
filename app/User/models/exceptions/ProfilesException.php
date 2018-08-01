<?php
namespace occ2\inventar\User\models\exceptions;

use occ2\model\EntityException as BaseException;

/**
 * UsersException
 * code interval 2200-2299
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
final class ProfilesException extends BaseException
{
    const PASSWORDS_NOT_SAME=2200, // exception when passwords on change passwords form not same
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
          UNAUTHORIZED_USER_DELETE=2215
    ;
}
