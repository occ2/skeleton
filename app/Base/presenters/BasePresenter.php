<?php
namespace occ2\inventar\presenters;

use Nette\Security\IUserStorage;

/**
 * BasePresenter parent of all logged presenters
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
abstract class BasePresenter extends AbstractPresenter
{
    public function startup()
    {
        parent::startup();
        if (!$this->user->isLoggedIn()) {
            if ($this->user->logoutReason === IUserStorage::INACTIVITY) {
                $this->flashMessage("user.signInForm.error.inactivity", static::STATUS_INFO, "", static::$iconPrefix . static::ICON_INFO);
            }
            $this->redirect(':User:Main:signIn', ['backlink' => $this->storeRequest()]);
        }
    }
}
