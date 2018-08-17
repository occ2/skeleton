<?php
namespace app\User\models\facades;

use app\User\models\entities\User as UserEntity;

/**
 * TUserDefaults
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
trait TUserDefaults
{
    /**
     * set defaults
     * @param UserEntity $user
     * @return string
     */
    protected function setDefaults(UserEntity $user)
    {
        $secret = Random::generate($this->config["randomSecretLength"]);
        $datetime = $this->datetimeFactory->create();
        $user->setStatus($this->config["defaultStatus"])
             ->setAttempts(0)
             ->setSecret($secret)
             ->setLang($this->config["defaultLang"])
             ->setPasswordExpiration($datetime->modify($this->config["passwordExpiration"]));
        return $secret;
    }
}