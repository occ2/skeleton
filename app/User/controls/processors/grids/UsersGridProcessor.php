<?php
namespace occ2\inventar\User\controls\grids;

use occ2\GridControl\IProcessor;
use occ2\GridControl\GridControl;
use Nette\Application\UI\Presenter;
use occ2\inventar\User\models\UsersFacade;
use occ2\inventar\User\models\AuthorizatorFacade;
use Ublaboo\DataGrid\DataGrid;
use occ2\inventar\User\presenters\ManagerPresenter;

/**
 * UsersGridProcessor
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class UsersGridProcessor implements IProcessor
{

    /**
     * users grid processor
     * @param GridControl $grid
     * @param Presenter $presenter
     * @return void
     */
    public function process(GridControl $grid, Presenter $presenter)
    {
        $this->setupOptions($grid);
        $this->setupChangeStatus($grid, $presenter);
        $this->setupToolbarButtonsConditions($grid, $presenter);
        $this->setupActions($grid, $presenter);
        $this->setupActionsConditions($grid, $presenter);
        return;
    }

    protected function setupToolbarButtonsConditions($grid,$presenter)
    {
        $grid->setAllowToolbarButtonCallback(UsersGrid::TOOLBAR_BUTTON_ADD,function () use ($presenter) {
            return $presenter->user->isAllowed(UsersFacade::ACL_RESOURCE_USER, AuthorizatorFacade::PRIVILEGE_ADD);
        } );
        return;
    }

    protected function setupChangeStatus($grid,$presenter)
    {
        $grid->setStatusChangeCallback(UsersGrid::STATUS,function (int $id,$value, UsersGrid $control) use ($presenter) {
            $presenter->usersFacade->userChangeStatus($id, $value);
            $presenter->flashMessage($control->text('user.success.changeStatus'), 'success');
            $presenter[ManagerPresenter::USERS_GRID]->reload();
            return;
        } );
        return;
    }

    protected function setupOptions($grid)
    {
        $grid->setLoadOptionsCallback(UsersGrid::STATUS,function ($control) {
            return [
                0=>$control->text("user.usersGrid.inactive"),
                1=>$control->text("user.usersGrid.active"),
            ];
        } );
        return;
    }

    protected function setupActions($grid,$presenter)
    {
        $grid->setActionCallback(UsersGrid::ACTION_DELETE,function($id,DataGrid $grid,$control) use ($presenter) {
            $presenter->usersFacade->deleteUser($id);
            $presenter->flashMessage($control->text("user.success.deleteUser"),'success');
            $presenter[ManagerPresenter::USERS_GRID]->reload();
        } );

        $grid->setActionCallback(UsersGrid::ACTION_RESET,function($id,DataGrid $grid,$control) use ($presenter) {
            $presenter->usersFacade->resetPassword($id);
            $presenter->flashMessage($control->text("user.success.resetAdmin"),'success');
            $presenter[ManagerPresenter::USERS_GRID]->reload();
        } );
        return;
    }

    protected function setupActionsConditions($grid,$presenter)
    {
        $grid->setAllowRowsActionCallback(UsersGrid::ACTION_EDIT,function() use ($presenter) :bool{
            return $presenter->user->isAllowed(UsersFacade::ACL_RESOURCE_USER, AuthorizatorFacade::PRIVILEGE_WRITE);
        });

        $grid->setAllowRowsActionCallback(UsersGrid::ACTION_HISTORY,function() use ($presenter) :bool{
            return $presenter->user->isAllowed(UsersFacade::ACL_RESOURCE_USER, AuthorizatorFacade::PRIVILEGE_READ);
        });

        $grid->setAllowRowsActionCallback(UsersGrid::ACTION_SETTINGS,function() use ($presenter) :bool{
            return $presenter->user->isAllowed(UsersFacade::ACL_RESOURCE_USER, AuthorizatorFacade::PRIVILEGE_READ);
        });

        $grid->setAllowRowsActionCallback(UsersGrid::ACTION_ROLES,function() use ($presenter) :bool{
            return $presenter->user->isAllowed(UsersFacade::ACL_RESOURCE_USER, AuthorizatorFacade::PRIVILEGE_WRITE);
        });

        $grid->setAllowRowsActionCallback(UsersGrid::ACTION_DELETE,function() use ($presenter) :bool{
            return $presenter->user->isAllowed(UsersFacade::ACL_RESOURCE_USER, AuthorizatorFacade::PRIVILEGE_DELETE);
        });

        $grid->setAllowRowsActionCallback(UsersGrid::ACTION_RESET,function() use ($presenter) :bool{
            return $presenter->user->isAllowed(UsersFacade::ACL_RESOURCE_USER, AuthorizatorFacade::PRIVILEGE_WRITE);
        });
        return;
    }
}