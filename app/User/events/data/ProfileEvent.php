<?php
namespace app\User\events\data;

use app\Base\events\Event;

/**
 * ProfileEvent
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class ProfileEvent extends Event
{
    const PASSWORD="password",
          SECRET="secret";
}