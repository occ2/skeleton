<?php
namespace app\User\models\facades;

use app\User\models\entities\User as UserEntity;
use Nette\Utils\Random;

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
     * @param bool $expiredPassword
     * @return string
     */
    protected function setDefaults(UserEntity $user,bool $expiredPassword=false): string
    {
        $secret = Random::generate($this->config["randomSecretLength"]);
        $datetime = $this->datetimeFactory->create();
        $user->setStatus($this->config["defaultStatus"])
             ->setAttempts(0)
             ->setSecret($secret)
             ->setLang($this->config["defaultLang"]);
        if($expiredPassword==true){
            $user->setPasswordExpiration($datetime);
        } else {
            $user->setPasswordExpiration($datetime->modify($this->config["passwordExpiration"]));
        }
        return $secret;
    }
}