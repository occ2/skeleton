<?php
namespace occ2\inventar\User\models\events;

use occ2\model\Event as BaseEvent;

/**
 * ResetEvent
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
final class ResetEvent extends BaseEvent
{
    const ID="id",
          EMAIL="email",
          USERMANE="username",
          NEW_PASSWORD="newPass";
}
