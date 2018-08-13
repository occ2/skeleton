<?php
namespace app\User\models\exceptions;

use app\User\models\exceptions\AbstractException as Exception;

/**
 * SettingsException
 * code interval 2600-2699
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class SettingsException extends Exception
{
    const NOT_FOUND=2601,
          MESSAGE_NOT_FOUND="user.error.settings.notFound";
}