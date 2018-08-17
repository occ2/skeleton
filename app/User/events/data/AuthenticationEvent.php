<?php
namespace app\User\events\data;

use app\Base\events\Event as BaseEvent;

/**
 * AuthenticationEvent
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class AuthenticationEvent extends BaseEvent
{
    const USERNAME="username",
          EMAIL="email",
          VALUES="values";
}