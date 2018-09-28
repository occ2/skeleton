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

namespace app\User\events\subscribers;

use Contributte\EventDispatcher\EventSubscriber;
use app\User\controls\grids\UserRolesGrid;
use app\Base\controls\GridControl\events\GridRowEventData;
use app\User\models\facades\RolesFacade;
use app\User\models\entities\Role as RoleEntity;
use app\User\models\exceptions\RolesException;
use app\Base\models\interfaces\ILogger;
use app\User\presenters\AdminPresenter;
use app\User\events\data\RolesEvent;
use app\User\models\facades\HistoryFacade;

/**
 * RolesEvents
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class RolesEvents implements EventSubscriber
{
    const MESSAGE_SUCCESS_ADD="user.success.roles.add",
          MESSAGE_SUCCESS_DELETE="user.success.roles.remove";

    /**
     * @var RolesFacade
     */
    private $rolesFacade;

    /**
     * @var HistoryFacade
     */
    private $historyFacade;

    /**
     * @param RolesFacade $rolesFacade
     * @param HistoryFacade $historyFacade
     * @return void
     */
    public function __construct(RolesFacade $rolesFacade, HistoryFacade $historyFacade)
    {
        $this->rolesFacade = $rolesFacade;
        $this->historyFacade = $historyFacade;
        return;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            UserRolesGrid::EVENT_SUCCESS_ADD=>"onSuccessAdd",
            UserRolesGrid::EVENT_DELETE_CONFIRM=>"onConfirmDelete",
            RolesFacade::EVENT_ADD=>"onRoleAdd",
            RolesFacade::EVENT_REMOVE=>"onRoleRemove"
        ];
    }

    /**
     * process inline add role
     * @param GridRowEventData $event
     * @throws RolesException
     * @return void
     */
    public function onSuccessAdd(GridRowEventData $event)
    {
        $control = $event->getControl();
        $presenter = $control->getPresenter();
        $role = $event->getData()->{RoleEntity::ROLE};
        $userId = $presenter->id;
        try {
            $this->rolesFacade->add($role,$userId);
            $control->flashMessage(
                self::MESSAGE_SUCCESS_ADD,
                AdminPresenter::STATUS_SUCCESS,
                null,
                AdminPresenter::ICON_SUCCESS,
                100
            );
            $control->reload();
        } catch (RolesException $exc) {
            if($exc->getCode()== RolesException::INVALID_ROLE){
                $control->flashMessage(
                    RolesException::MESSAGE_INVALID_ROLE,
                    AdminPresenter::STATUS_DANGER,
                    null,
                    AdminPresenter::ICON_DANGER,
                    100
                );
                $control->reload();
            } elseif ($exc->getCode()== RolesException::ROLE_IS_IN_USE) {
                $control->flashMessage(
                    RolesException::MESSAGE_ROLE_IN_USE,
                    AdminPresenter::STATUS_DANGER,
                    null,
                    AdminPresenter::ICON_DANGER,
                    100
                );
                $control->reload();
            } else {
                throw $exc;
            }
        }
    }

    /**
     * process delete role action
     * @param GridRowEventData $event
     * @throws RolesException
     * @return void
     */
    public function onConfirmDelete(GridRowEventData $event)
    {
        $control = $event->getControl();
        $id = $event->getId();
        try {
            $this->rolesFacade->remove($id);
            $control->flashMessage(
                self::MESSAGE_SUCCESS_DELETE,
                AdminPresenter::STATUS_SUCCESS,
                null,
                AdminPresenter::ICON_SUCCESS,
                100
            );
            $control->reload();
        } catch (RolesException $exc) {
            if($exc->getCode()== RolesException::NOT_FOUND){
                $control->flashMessage(
                    RolesException::MESSAGE_NOT_FOUND,
                    AdminPresenter::STATUS_DANGER,
                    null,
                    AdminPresenter::ICON_DANGER,
                    100
                );
                $control->reload();
            } else {
                throw $exc;
            }
        }
    }

    /**
     * on role add event - save into history
     * @param RolesEvent $event
     * @return void
     * @todo inform user by email - depends on user settings
     */
    public function onRoleAdd(RolesEvent $event)
    {
        $user = $event->entity->getUser();
        $this->historyFacade->save(
            $user,
            self::MESSAGE_SUCCESS_ADD,
            ILogger::INFO,
            [RoleEntity::ROLE=>$event->entity->getRole()]
        );
    }

    /**
     * on role remove event - save into history
     * @param RolesEvent $event
     * @return void
     * @todo inform user by email - depends on user settings
     */
    public function onRoleRemove(RolesEvent $event)
    {
        $user = $event->entity->getUser();
        $this->historyFacade->save(
            $user,
            self::MESSAGE_SUCCESS_DELETE,
            ILogger::INFO,
            [RoleEntity::ROLE=>$event->entity->getRole()]
        );
    }
}
