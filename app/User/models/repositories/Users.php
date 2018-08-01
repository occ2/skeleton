<?php
namespace occ2\inventar\User\models\repositories;

use occ2\model\BaseRepository;

/**
 * Users
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
final class Users extends BaseRepository
{
    const ID="id",
          USERNAME="username",
          NAME="realname",
          EMAIL="email",
          PHONE="phone",
          PASSWORD_HASH="passwordHash",
          PASSWORD_EXPIRATION="passwordExpiration",
          STATUS="status",
          ATTEMPTS="attempts",
          CONTROL_QUESTION="cQuestion",
          CONTROL_ANSWER="cAnswer",
          SECRET ="secret",
          LANG="lang";
}
