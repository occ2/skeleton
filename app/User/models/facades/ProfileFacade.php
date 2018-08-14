<?php
namespace app\User\models\facades;

use app\Base\models\entities\IEntity;
use app\User\models\facades\BaseFacade;
use app\User\models\facades\TUserDefaults;
use app\User\events\data\ProfileEvent;
use app\User\models\entities\User as UserEntity;
use app\User\models\exceptions\ProfileException;
use Nette\Utils\Random;

/**
 * ProfileFacade
 * class for user data manipulation
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class ProfileFacade extends BaseFacade
{
    use TUserDefaults;

    const ENTITY_CLASS=UserEntity::class,
          EVENT_FIND="User.ProfileFacade.onFind",
          EVENT_REGISTER="User.ProfileFacade.onRegister",
          EVENT_SAVE="User.ProfileFacade.onSave",
          EVENT_ADD="User.ProfileFacade.onAdd";

    /**
     * @var array
     */
    protected $config=[
        "randomPasswordLength"=>8,
        "randomSecretLength"=>8,
        "passwordExpiration"=>"+90 Days",
        "defaultStatus"=>1,
        "defaultLang"=>"cz"
    ];

    /**
     * find user by id
     * @param int $id
     * @return IEntity
     */
    public function find(int $id,bool $throwException=true)
    {
        $exceptionClass = $throwException==true ? ProfileException::class : null;
        $user = parent::get($id, $exceptionClass);
        $this->on(
            self::EVENT_FIND,
            new ProfileEvent(
                [
                    ProfileEvent::ENTITY=>$user
                ],
                self::EVENT_FIND
            )
        );
        return $user;
    }

    /**
     * save user changes
     * @param array $data
     * @param array $exclude
     * @return void
     */
    public function save(array $data,array $exclude=[])
    {
        $user = $this->get($data[UserEntity::ID], ProfileException::class);
        $this->modify($user, $data, $exclude);
        $this->em->flush();
        $this->on(
            self::EVENT_SAVE,
            new ProfileEvent(
                [
                    ProfileEvent::ENTITY=>$user
                ],
                self::EVENT_SAVE
            )
        );
        return;
    }

    /**
     * add user
     * @param array $data
     * @param array $exclude
     * @return void
     */
    public function add(array $data,array $exclude=[])
    {
        $u = $this->loadUser($data[UserEntity::USERNAME],false);
        if($u!=null){
            throw new ProfileException(ProfileException::MESSAGE_NOT_UNIQUE, ProfileException::USERNAME_NOT_UNIQUE);
        }
        $password = Random::generate($this->config["randomPasswordLength"]);
        $user = $this->create($data,$exclude);
        $secret = $this->setDefaults($user);
        $user->setPassword($password);
        $this->em->persist($user);
        $this->em->flush($user);
        $this->on(
            self::EVENT_ADD,
            new ProfileEvent(
                [
                    ProfileEvent::ENTITY=>$user,
                    ProfileEvent::PASSWORD=>$password,
                    ProfileEvent::SECRET=>$secret
                ],
                self::EVENT_ADD
            )
        );
        return;
    }

    /**
     * register user
     * @param array $data
     * @param array $exclude
     * @return void
     */
    public function register(array $data,array $exclude=[])
    {
        $u = $this->loadUser($data[UserEntity::USERNAME],false);
        if($u!=null){
            throw new ProfileException(ProfileException::MESSAGE_NOT_UNIQUE, ProfileException::USERNAME_NOT_UNIQUE);
        }
        $user = $this->create($data, $exclude);
        $secret = $this->setDefaults($user);
        $this->em->persist($user);
        $this->em->flush($user);
        $this->on(
            self::EVENT_REGISTER,
            new ProfileEvent(
                [
                    ProfileEvent::ENTITY=>$user,
                    ProfileEvent::SECRET=>$secret
                ],
                self::EVENT_REGISTER)
            );
        return;
    }
}