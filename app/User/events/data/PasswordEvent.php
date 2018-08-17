<?php
namespace app\User\events\data;

use app\Base\events\Event as AbstractEvent;

/**
 * PasswordEvent
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class PasswordEvent extends AbstractEvent
{
    const PASSWORD="password";
}