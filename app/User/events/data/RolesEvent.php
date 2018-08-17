<?php
namespace app\User\events\data;

use app\Base\events\Event;

/**
 * RolesEvent
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class RolesEvent extends Event
{
    const ROLES="roles",
          USER="user";
}