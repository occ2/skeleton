<?php
namespace occ2\inventar\User\presenters;

use occ2\inventar\presenters\BasePresenter as Presenter;
use occ2\inventar\User\controls\forms\SettingsForm;
use occ2\inventar\User\controls\forms\ChangePassForm;

/**
 * ProfilePresenter
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
final class ProfilePresenter extends Presenter
{
    const SETTINGS_FORM="settingsForm",
          CHANGE_PASS_FORM="changePassForm",
          USER_CONFIG_GRID="userConfigGrid",
          ACTION_PASSWORD="password";

    /** @persistent */
    public $backlink = '';
    
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
     * @var \occ2\inventar\User\controls\forms\ISettingsForm
     */
    public $settingsFormFactory;

    /**
     * @inject
     * @var \occ2\inventar\User\controls\forms\SettingsFormProcessor
     */
    public $settingsFormProcessor;

    /**
     * @inject
     * @var \occ2\inventar\User\controls\forms\IChangePassForm
     */
    public $changePassFormFactory;

    /**
     * @inject
     * @var \occ2\inventar\User\controls\forms\ChangePassFormProcessor
     */
    public $changePassFormProcessor;

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
     * @var int
     */
    public $userId;
    
    /**
     * startup (load properties from session etc.)
     * @return void
     */
    public function startup()
    {
        parent::startup();
        if ($this->user->isLoggedIn()) {
            if ($this->user->identity->currentAccount) {
                $this->user->getAuthorizator()->setAccount($this->user->identity->currentAccount);
            }
        }
        return;
    }
    
    /**
     * settings form factory and setup
     * @return \occ2\inventar\User\controls\forms\SettingsForm
     */
    public function createComponentSettingsForm()
    {
        $form = $this->settingsFormFactory->create();
        $this->settingsFormProcessor->process($form, $this);
        return $form;
    }
    
    /**
     * change pass form factory and setup
     * @return \occ2\inventar\User\controls\forms\ChangePassForm
     */
    public function createComponentChangePassForm()
    {
        $form = $this->changePassFormFactory->create();
        $this->changePassFormProcessor->process($form,$this);
        return $form;
    }

    /**
     * user config grid factory and setup
     * @return \occ2\inventar\User\controls\grids\UserConfigGrid
     */
    public function createComponentUserConfigGrid()
    {
        $grid = $this->userConfigGridFactory->create();
        $grid->setDatasource($this->usersConfigFacade->getUserConfig($this->user->getId()));
        $this->userConfigGridProcessor->process($grid,$this);
        return $grid;
    }
    
    /**
     * default action
     * @return void
     * @acl (loggedIn=true)
     * @title user.navbar.profile
     */
    public function actionDefault()
    {
        $this[self::BREADCRUMBS]->active("home");
        $this[self::BREADCRUMBS]->addItem("profileDefault","user.navbar.profile",$this->link(":User:Profile:default"));
        $defaults=$this->user->getIdentity()->getData();
        unset($defaults[SettingsForm::ANSWER]);
        $this[self::SETTINGS_FORM]->setDefaults($defaults);
        return;
    }
    
    /**
     * change password action
     * @return void
     * @acl (loggedIn=true)
     * @title user.navbar.password
     */
    public function actionPassword()
    {
        $this[self::BREADCRUMBS]->active("home");
        $this[self::BREADCRUMBS]->addItem("profilePassword","user.navbar.password",$this->link(":User:Profile:password"));

        $defaults[ChangePassForm::ID]=$this->user->getIdentity()->getId();
        $this[self::CHANGE_PASS_FORM]->setDefaults($defaults);
        return;
    }

    /**
     * change user settings action
     * @return void
     * @title user.navbar.config
     */
    public function actionConfig()
    {
        $this[self::BREADCRUMBS]->active("home");
        $this[self::BREADCRUMBS]->addItem("profileConfig","user.navbar.config",$this->link(":User:Profile:config"));
        return;
    }
}
