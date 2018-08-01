<?php
namespace occ2\inventar\User\models\repositories;

use occ2\model\BaseRepository;

/**
 * UsersConfig
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
final class UsersConfig extends BaseRepository
{
    const ID="id",
          USER="Users_id",
          COMMENT="comment",
          KEY="key",
          VALUE="value",
          TYPE="type";
}
