<?php
namespace occ2\inventar\User\controls\grids;

use occ2\GridControl\IProcessor;
use occ2\GridControl\GridControl;
use Nette\Application\UI\Presenter;
use occ2\inventar\User\presenters\ProfilePresenter;
use occ2\inventar\User\presenters\ManagerPresenter;
use occ2\inventar\User\models\repositories\UsersConfig;

/**
 * UserHistoryGridProcessor
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class UserConfigGridProcessor implements IProcessor
{

    /**
     * datagrid processor
     * @param GridControl $grid
     * @param Presenter $presenter
     * @return void
     */
    public function process(GridControl $grid, Presenter $presenter)
    {
        $this->valueEditSetter($grid, $presenter);
        $this->valueInputTypeSetter($grid);
        $this->setValueRenderer($grid);
        $this->resetToolbarBtn($grid,$presenter);
        $this->reloadToolbarBtn($grid,$presenter);
        return;
    }

    protected function valueEditSetter($grid,$presenter)
    {
        $grid->setEditableCallback(UserConfigGrid::VALUE,function($id,$value) use ($presenter) {
            $presenter->usersConfigFacade->updateConfig($id,$value);
            $presenter[ProfilePresenter::USER_CONFIG_GRID]->flashMessage("user.success.editConfig", "success",null,null,100);
            $presenter[ProfilePresenter::USER_CONFIG_GRID]->reload();
            return;
        });
    }

    protected function valueInputTypeSetter($grid)
    {
        $grid->setColumnCallback(UserConfigGrid::VALUE,function($column, $item, $control) {
            if($item->{UsersConfig::TYPE}=="bool"){
                $column->setEditableInputTypeSelect([
		0 => $control->text(UserConfigGrid::YES_NO[0]),
		1 => $control->text(UserConfigGrid::YES_NO[1]),
            ]);
            } else {
                $column->setEditableInputType('text', ['class' => 'form-control']);
            }
        });
        return;
    }

    protected function resetToolbarBtn($grid,$presenter)
    {
        $grid->setToolbarButtonCallback(UserConfigGrid::TOOLBAR_BUTTON_RESET,function() use ($presenter) {

            $presenter->usersConfigFacade->resetConfig(
                $presenter->userId==null ? $presenter->user->getId() : $presenter->userId
            );
            $presenter[ProfilePresenter::USER_CONFIG_GRID]->flashMessage("user.success.configReset", "success",null,null,100);
            $presenter[ProfilePresenter::USER_CONFIG_GRID]->reload();
        });
        return;
    }

    protected function reloadToolbarBtn($grid,$presenter)
    {   
        $grid->setToolbarButtonCallback(UserConfigGrid::TOOLBAR_BUTTON_RELOAD,function() use ($presenter) {
            $presenter->usersConfigFacade->reloadConfig(
                $presenter->userId==null ? $presenter->user->getId() : $presenter->userId
            );
            $presenter[ProfilePresenter::USER_CONFIG_GRID]->flashMessage("user.success.configReload", "success",null,null,100);
            $presenter[ProfilePresenter::USER_CONFIG_GRID]->reload();
        });

        $grid->setAllowToolbarButtonCallback(UserConfigGrid::TOOLBAR_BUTTON_RELOAD,function() use ($presenter) {
            if($presenter instanceof ManagerPresenter){
                return true;
            } else {
                return false;
            }
        });

        return;
    }

    protected function setValueRenderer($grid)
    {
        $grid->setColumnRendererCallback(UserConfigGrid::VALUE,function($item,$control) {
            if($item->{UsersConfig::TYPE}=="bool"){
                return $control->text(UserConfigGrid::YES_NO[$item->{UsersConfig::VALUE}]);
            } else {
                return $item->{UsersConfig::VALUE};
            }
        });
        return;
    }
}