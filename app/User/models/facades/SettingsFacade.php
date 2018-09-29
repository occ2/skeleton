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

use app\Base\models\facades\BaseFacade;
use app\User\models\entities\Settings as SettingsEntity;
use app\User\models\entities\User as UserEntity;
use app\User\models\exceptions\SettingsException;
use app\User\models\exceptions\ProfileException;
use app\User\events\data\SettingsEvent;
use Doctrine\Common\Collections\Collection;

/**
 * SettingsFacade
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class SettingsFacade extends BaseFacade
{
    const ENTITY_CLASS=SettingsEntity::class,
          EVENT_SAVE="User.SettingsEvents.onSave",
          EVENT_FIND="User.SettingsEvents.onFind",
          EVENT_LOAD="User.SettingsEvents.onLoad",
          EVENT_RESET="User.SettingsEvents.onReset",
          EVENT_RELOAD="User.SettingsEvents.onReload",
          EVENT_SET_DEFAULT="User.SettingsEvents.onSetDefault";

    /**
     * find one settings item
     * @param int $id
     * @return SettingsEntity | null
     */
    public function find(int $id): ?SettingsEntity
    {
        // try to find settings item
        $settings = $this->em->find(SettingsEntity::class, $id);

        // test if found, if not throw exception
        $this->testFound($settings, SettingsException::class);

        // fire event
        $this->on(
            static::EVENT_FIND,
            new SettingsEvent(
                [
                    SettingsEvent::ENTITY=>$settings
                ],
                static::EVENT_FIND
            )
        );

        return $settings;
    }


    /**
     * load settings by user
     * @param int | UserEntity $user
     * @return Collection | null
     */
    public function load($user): ?Collection
    {
        // try to find settings of user
        $this->testFound($user, ProfileException::class);
        if($user instanceof UserEntity){
            $settings = $user->getSettings();
        } else {
            $user = $this->em->find(UserEntity::class, $user);
            $settings = $user==null ? null : $user->getSettings();
        }
        
        // fire event
        $this->on(
            static::EVENT_LOAD,
            new SettingsEvent(
                [
                    SettingsEvent::COLLECTION=>$settings
                ],
                static::EVENT_LOAD
            )
        );
        return $settings;
    }

    /**
     * save user settings
     * @param int $id
     * @param mixed $value
     * @return void
     */
    public function save(int $id,$value)
    {
        // find settings item
        $entity = $this->em->find(SettingsEntity::class, $id);
        $this->testFound($entity, SettingsException::class);

        if($entity!=null){
            if($entity->getType()=="bool"){
                if($value>1){
                    $value=1;
                } elseif ($value<0){
                    $value=0;
                }
            }
            // set new value
            $entity->setValue($value);

            // save to DB
            $this->em->flush();
        }

        // fire event
        $this->on(
            static::EVENT_SAVE,
            new SettingsEvent(
                [
                    SettingsEvent::ENTITY=>$entity
                ],
                static::EVENT_SAVE
            )
        );
        return;
    }

    /**
     * reset user setting to default values
     * @param int $userId
     * @return void
     */
    public function reset(int $userId)
    {
        // find user
        $user = $this->em->find(UserEntity::class, $userId);
        $this->testFound($user, ProfileException::class);
        if($user!=null){
            // iterate default config
            foreach($this->config as $config){
                // try to find config
                $settings = $this->em
                                 ->getRepository(SettingsEntity::class)
                                 ->findOneBy([
                                     SettingsEntity::USER=>$user,
                                     SettingsEntity::KEY=>$config["key"]
                                 ]);

                // if not found add new by default values
                if($settings==null && $user!=null){
                    $entity = new SettingsEntity;
                    $entity->fill($config);
                    $entity->setUser($user);
                    $this->em->persist($entity);
                } else {
                // if found set value from config
                   $settings->setValue((string) $config["value"]);
                }
            }

            // save to DB
            $this->em->flush();
        }

        // fire event
        $this->on(
            static::EVENT_RESET,
            new SettingsEvent(
                [
                    SettingsEvent::COLLECTION=>$user instanceof UserEntity ? $user->getSettings() : null
                ],
                static::EVENT_RESET
            )
        );
        return;
    }

    /**
     * reload user settings (preserve old and add new)
     * @param int $userId
     * @return void
     */
    public function reload(int $userId)
    {
        // find user
        $user = $this->em->find(UserEntity::class, $userId);
        $this->testFound($user, ProfileException::class);

        // iterate default config
        foreach($this->config as $config){
            // try to find config
            $settings = $this->em
                             ->getRepository(SettingsEntity::class)
                             ->findOneBy([
                                 SettingsEntity::USER=>$user,
                                 SettingsEntity::KEY=>$config["key"]
                             ]);

            // if not found add new by default values
            if($settings==null && $user!=null){
                $entity = new SettingsEntity;
                $entity->fill($config);
                $entity->setUser($user);
                $this->em->persist($entity);
            }
        }

        // save to DB
        $this->em->flush();

        // fire event
        $this->on(
            static::EVENT_RELOAD,
            new SettingsEvent(
                [
                    SettingsEvent::COLLECTION=>$user!=null ? $user->getSettings() : null
                ],
                static::EVENT_RELOAD
            )
        );
        return;
    }
}