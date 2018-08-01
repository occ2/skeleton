<?php
namespace occ2\inventar\User\controls\grids;

use Nette\Application\UI\Presenter;
use Nette\Forms\Container;
use occ2\GridControl\IProcessor;
use occ2\GridControl\GridControl;
use occ2\inventar\User\models\UsersFacade;
use occ2\inventar\User\models\AuthorizatorFacade;
use occ2\inventar\User\presenters\ManagerPresenter;
use occ2\GridControl\DataGrid;
use occ2\inventar\User\models\exceptions\RolesException;

/**
 * UserRolesGridProcessor
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class UserRolesGridProcessor implements IProcessor
{

    /**
     * datagrid processor
     * @param GridControl $grid
     * @param Presenter $presenter
     * @return void
     */
    public function process(GridControl $grid, Presenter $presenter)
    {
        $this->setupComment($grid, $presenter);
        $this->setupDeleteBtn($grid, $presenter);
        $this->setupInlineAdd($grid, $presenter);
        //$this->setupInlineEdit($grid, $presenter);
        return;
    }

    protected function setupComment($grid,$presenter)
    {
        $grid->setColumnRendererCallback(UserRolesGrid::COMMENT,function($item) use ($presenter){
            if($item->role=="guest" || $item->role=="authenticated" || $item->role=="administrator"){
                return $presenter->_("base.roles." . $item->role);
            } else {
                return $presenter->_($item->role);
            }
        });
        return;
    }

    protected function setupInlineAdd($grid,$presenter)
    {
        $grid->setInlineLoadOptionsCallback(UserRolesGrid::ROLE,function() use ($presenter){
            return $presenter->rolesFacade->listRoles();
        });
        $grid->setInlineAddSubmitCallback(function($values) use ($presenter) {
            try {
                $presenter->rolesFacade->addRole($presenter->userId,$values->{UserRolesGrid::ROLE});
                $presenter[ManagerPresenter::USER_ROLES_GRID]->flashMessage("user.success.addRole","success");
                $presenter[ManagerPresenter::USER_ROLES_GRID]->reload();
            } catch (RolesException $exc) {
                $presenter->flashMessage($exc->getMessage(),"danger");
                $presenter->redirect("this");
            }
        });
    }
/*
    protected function setupInlineEdit($grid,$presenter)
    {
        $grid->setInlineLoadOptionsCallback(UserRolesGrid::ROLE,function() use ($presenter){
            return $presenter->rolesFacade->listRoles();
        });
        $grid->setInlineFormFillCallback(function(Container $form,$item,$control) use ($presenter){
            $form->setDefaults([
                UserRolesGrid::ROLE=>$item->{UserRolesGrid::ROLE}
            ]);
        });
        $grid->setInlineEditSubmitCallback(function($id,$values,$control) use ($presenter){
            try {
                $presenter[ManagerPresenter::USER_ROLES_GRID]->flashMessage("user.success.addRole","success");
                $presenter[ManagerPresenter::USER_ROLES_GRID]->reload();
            } catch (RolesException $exc) {
                $presenter->flashMessage($exc->getMessage(),"danger");
                $presenter->redirect("this");
            }
        });
    }
*/

    protected function setupDeleteBtn($grid,$presenter)
    {
        $grid->setActionCallback(UserRolesGrid::ACTION_DELETE,function($id,DataGrid $grid,$control) use ($presenter) {
            $presenter->rolesFacade->removeRole($id);
            $presenter->flashMessage($control->text("user.success.removeRole"),'success');
            $presenter[ManagerPresenter::USER_ROLES_GRID]->reload();
            return;
        } );

        $grid->setAllowRowsActionCallback(UserRolesGrid::ACTION_DELETE,function() use ($presenter) : bool{
            return $presenter->user->isAllowed(UsersFacade::ACL_RESOURCE_USER, AuthorizatorFacade::PRIVILEGE_WRITE);
        });
    }
}