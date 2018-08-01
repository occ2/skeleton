<?php
namespace app\Base\presenters;

use Nette\Security\IUserStorage;

/**
 * BasePresenter parent of all logged presenters
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
abstract class BasePresenter extends AbstractPresenter
{
    /**
     * test user logged in, if not redirect to sign in page
     * @return void
     */
    public function startup()
    {
        parent::startup();
        if (!$this->user->isLoggedIn()) {
            if ($this->user->logoutReason === IUserStorage::INACTIVITY) {
                $this->flashMessage("user.signInForm.error.inactivity", static::STATUS_INFO, "", static::$iconPrefix . static::ICON_INFO);
            }
            $this->redirect(self::SIGN_IN_LINK, ['backlink' => $this->storeRequest()]);
        }
        return;
    }
}
