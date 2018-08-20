<?php
/*
 * The MIT License
 *
 * Copyright 2018 Milan Onderka <milan_onderka@occ2.cz>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace app\User\models\facades;

use app\User\models\facades\BaseFacade;
use app\User\models\facades\TUserDefaults;
use app\User\events\data\ProfileEvent;
use app\User\models\entities\User as UserEntity;
use app\User\models\exceptions\ProfileException;
use app\User\models\facades\TTestUser;

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
    use TTestUser;

    const EVENT_FIND="User.ProfileFacade.onFind",
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
     * save user changes
     * @param array $data
     * @param array $exclude
     * @return void
     */
    public function save(array $data,array $exclude=[])
    {
        // find user
        $user = $this->em->find(UserEntity::class, $data[UserEntity::ID]);
        $this->testFound($user, ProfileException::class);
        if($user!=null){
            // test of user is same as logged in user
            $this->testUser($user);
            // modify entity data
            $this->modify($user, $data, $exclude);
            // save to DB
            $this->em->flush();
            // fire event
            $this->on(
                self::EVENT_SAVE,
                new ProfileEvent(
                    [
                        ProfileEvent::ENTITY=>$user
                    ],
                    self::EVENT_SAVE
                )
            );
        }
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
        // test if registerd username is unique
        $u = $this->loadUser($data[UserEntity::USERNAME],false);
        if($u!=null){
            throw new ProfileException(ProfileException::MESSAGE_NOT_UNIQUE, ProfileException::USERNAME_NOT_UNIQUE);
        }
        // create new entity
        $user = new UserEntity;
        // fill it with data
        $user->fill($this->exclude($data, $exclude));
        // set default data and create secret
        $secret = $this->setDefaults($user);
        // save to DB
        $this->em->persist($user);
        $this->em->flush($user);
        // fire event
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