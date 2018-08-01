<?php
namespace occ2\inventar\User\models\repositories;

use occ2\model\BaseRepository;

/**
 * UsersHistory
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
final class UsersHistory extends BaseRepository
{
    const ID="id";
    const USER="Users_id";
    const DATETIME="datetime";
    const TYPE="type";
    const MESSAGE="message";
    const DATA="data";
}
