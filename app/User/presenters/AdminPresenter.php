<?php
namespace app\User\presenters;

/**
 * AdminPresenter
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class AdminPresenter extends BasePresenter
{
    private $id;

    public function actionDefault()
    {

    }

    public function actionHistory($id)
    {
        $this->id = $id;
    }

    public function actionSettings($id)
    {
        $this->id = $id;
    }

    public function actionRoles($id)
    {
        $this->id = $id;
    }

    public function actionAdd()
    {
    }

    public function actionEdit($id)
    {
        $this->id = $id;
    }

    public function actionReset($id)
    {
        $this->id = $id;
    }

    public function createComponentUsersForm()
    {

    }

    public function createComponentUsersGrid()
    {

    }

    public function createComponentHistoryGrid()
    {

    }

    public function createComponentSettingsGrid()
    {

    }

    public function createComponentRolesGrid()
    {

    }

    public function createComponentResetDialog()
    {

    }
}