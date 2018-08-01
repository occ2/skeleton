<?php
namespace occ2\inventar\User\presenters;

use occ2\inventar\presenters\BasePresenter as Presenter;
use occ2\inventar\User\models\repositories\Users;

/**
 * ManagerPresenter
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
final class ManagerPresenter extends Presenter
{
    const USER_FORM="userForm",
          USERS_GRID="usersGrid",
          USER_HISTORY_GRID="userHistoryGrid",
          USER_CONFIG_GRID="userConfigGrid",
          USER_ROLES_GRID="userRolesGrid",
          ACTION_EDIT="edit",
          ACTION_ADD="add";
    
    /**
     * @inject
     * @var \occ2\inventar\User\models\facades\Profiles
     */
    public $usersFacade;

    /**
     * @inject
     * @var \occ2\inventar\User\models\UsersConfigFacade
     */
    public $usersConfigFacade;

    /**
     * @inject
     * @var \occ2\inventar\User\models\RolesFacade
     */
    public $rolesFacade;

    /**
     * @inject
     * @var \occ2\inventar\User\controls\forms\IUserForm
     */
    public $userAddFormFactory;
    
    /**
     * @inject
     * @var \occ2\inventar\User\controls\grids\IUsersGrid
     */
    public $usersGridFactory;

    /**
     * @inject
     * @var \occ2\inventar\User\controls\grids\UsersGridProcessor
     */
    public $usersGridProcessor;

    /**
     * @inject
     * @var \occ2\inventar\User\controls\forms\UserFormProcessor
     */
    public $userFormProcessor;

    /**
     * @inject
     * @var \occ2\inventar\User\controls\grids\IUserHistoryGrid
     */
    public $userHistoryGridFactory;

    /**
     * @inject
     * @var \occ2\inventar\User\controls\grids\UserHistoryGridProcessor
     */
    public $userHistoryGridProcessor;

    /**
     * @inject
     * @var \occ2\inventar\User\controls\grids\IUserConfigGrid
     */
    public $userConfigGridFactory;

    /**
     * @inject
     * @var \occ2\inventar\User\controls\grids\UserConfigGridProcessor
     */
    public $userConfigGridProcessor;

    /**
     * @inject
     * @var \occ2\inventar\User\controls\grids\IUserRolesGrid
     */
    public $userRolesGridFactory;

    /**
     * @inject
     * @var \occ2\inventar\User\controls\grids\UserRolesGridProcessor
     */
    public $userRolesGridProcessor;

    /**
     * @var string
     */
    public $title="user.usersGrid.title";

    /**
     * @var int
     */
    public $userId=null;

    /**
     * users grid factory and setup
     * @return \occ2\inventar\User\controls\grids\UsersGrid
     */
    public function createComponentUsersGrid()
    {
        $grid = $this->usersGridFactory->create();
        $grid->setDatasource($this->usersFacade->allUsers());
        $this->usersGridProcessor->process($grid, $this);
        return $grid;
    }

    /**
     * user history grid factory and setup
     * @return \occ2\inventar\User\controls\grids\UserHistoryGrid
     */
    public function createComponentUserHistoryGrid()
    {
        $grid = $this->userHistoryGridFactory->create();
        $grid->setDatasource($this->usersFacade->getHistory($this->userId));
        $this->userHistoryGridProcessor->process($grid, $this);
        return $grid;
    }

    /**
     * user config grid factory and setup
     * @return \occ2\inventar\User\controls\grids\UserConfigGrid
     */
    public function createComponentUserConfigGrid()
    {
        $grid = $this->userConfigGridFactory->create();
        $grid->setDatasource($this->usersConfigFacade->getUserConfig($this->userId));
        $this->userConfigGridProcessor->process($grid, $this);
        return $grid;
    }

    public function createComponentUserRolesGrid()
    {
        $grid = $this->userRolesGridFactory->create();
        $grid->setDatasource($this->rolesFacade->getRoles($this->userId));
        $this->userRolesGridProcessor->process($grid, $this);
        return $grid;
    }

    /**
     * users form factory and setup
     * @return \occ2\inventar\User\controls\forms\UserForm
     */
    public function createComponentUserForm()
    {
        $form = $this->userAddFormFactory->create();
        $this->userFormProcessor->process($form,$this);
        return $form;
    }

    /**
     * default action - show users grid
     * @return void
     * @title user.navbar.management
     */
    public function actionDefault()
    {
        $this[self::BREADCRUMBS]->active("home");
        $this[self::BREADCRUMBS]->addItem("managerDefault","user.navbar.management",$this->link(":User:Manager:default"));
        return;
    }

    /**
     * show user form (add)
     * @return void
     * @title user.addUserForm.title
     * @acl (resource="users",privilege="add")
     */
    public function actionAdd()
    {
        $this[self::BREADCRUMBS]->active("home");
        $this[self::BREADCRUMBS]->addItem("managerDefault","user.navbar.management",$this->link(":User:Manager:default"),true);
        $this[self::BREADCRUMBS]->addItem("managerAdd","user.addUserForm.title",$this->link(":User:Manager:default"));
        return;
    }

    /**
     * show user form (edit)
     * @return void
     * @acl (resource="users",privilege="write")
     */
    public function actionEdit(int $id)
    {
        $user = $this->usersFacade->getUser($id);
        $this->title = $this->_("user.usersGrid.edit") . " - " . $user->{Users::NAME};
        $this[self::USER_FORM]->setTitle($this->title);
        $this[self::BREADCRUMBS]->active("home");
        $this[self::BREADCRUMBS]->addItem("managerDefault","user.navbar.management",$this->link(":User:Manager:default"),true);
        $this[self::BREADCRUMBS]->addItem("managerEdit",$this->title,$this->link(":User:Manager:default"));
        $this[static::USER_FORM]->setDefaults($this->usersFacade->getUser($id));
        return;
    }

    /**
     * show modal with user history grid
     * @acl (resource="users",privilege="read")
     * @return void
     */
    public function actionHistory(int $id)
    {
        $this->userId = $id;
        $user = $this->usersFacade->getUser($id);
        $this->title = $this->_($this[self::USER_HISTORY_GRID]->getTitle()) . " - " . $user->{Users::NAME};
        $this[self::BREADCRUMBS]->active("home");
        $this[self::BREADCRUMBS]->addItem("managerDefault","user.navbar.management",$this->link(":User:Manager:default"),true);
        $this[self::BREADCRUMBS]->addItem("managerHistory",$this->title,$this->link(":User:Manager:default"));
        $this[self::USER_HISTORY_GRID]->setTitle($this->title);
        return;
    }

    /**
     * show user settings grid
     * @acl (resource="users",privilege="read")
     * @return void
     */
    public function actionSettings(int $id)
    {
        $this->userId = $id;
        $user = $this->usersFacade->getUser($id);
        $this->title = $this->_($this[self::USER_CONFIG_GRID]->getTitle()) . " - " . $user->{Users::NAME};
        $this[self::BREADCRUMBS]->active("home");
        $this[self::BREADCRUMBS]->addItem("managerDefault","user.navbar.management",$this->link(":User:Manager:default"),true);
        $this[self::BREADCRUMBS]->addItem("managerSettings",$this->title,$this->link(":User:Manager:default"));
        return;
    }

    /**
     * show user roles grid
     * @param int $id
     * @acl (resource="users",privilege="read")
     * @return void
     * @return void
     */
    public function actionRoles(int $id)
    {
        $this->userId = $id;
        $user = $this->usersFacade->getUser($id);
        $this->title = $this->_($this[self::USER_ROLES_GRID]->getTitle()) . " - " . $user->{Users::NAME};
        $this[self::BREADCRUMBS]->active("home");
        $this[self::BREADCRUMBS]->addItem("managerDefault","user.navbar.management",$this->link(":User:Manager:default"),true);
        $this[self::BREADCRUMBS]->addItem("managerRoles",$this->title,$this->link(":User:Manager:default"));
        return;
    }
}
