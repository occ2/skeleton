<?php
namespace occ2\inventar\User\presenters;

use occ2\inventar\User\controls\forms\ResetPassForm;
use occ2\inventar\User\controls\forms\ControlQuestionForm;

/**
 * MainPresenter
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
final class MainPresenter extends BasePresenter
{
    const SIGN_IN_FORM="signInForm",
          REGISTER_FORM="registerForm",
          RESET_PASS_FORM="resetPassForm",
          CONTROL_QUESTION_FORM="controlQuestionForm",
          CONTROL_RECAPTCHA_FORM="controlRecapchaForm",
          EXPIRED_PASS_FORM="expiredPassForm",
          USER_HISTORY_GRID="userHistoryGrid",
          ACTION_RESET="reset",
          ACTION_EXPIRED="expired",
          ACTION_SIGNIN="signIn";
    
    /**
     * @inject
     * @var \occ2\inventar\User\controls\forms\ISignInForm
     */
    public $signInFormFactory;

    /**
     * @inject
     * @var \occ2\inventar\User\controls\forms\SignInFormProcessor
     */
    public $signInFormProcessor;
    
    /**
     * @inject
     * @var \occ2\inventar\User\controls\forms\IRegisterForm
     */
    public $registerFormFactory;

    /**
     * @inject
     * @var \occ2\inventar\User\controls\forms\RegisterFormProcessor
     */
    public $registerFormProcessor;

    /**
     * @inject
     * @var \occ2\inventar\User\controls\forms\ResetPassFormProcessor
     */
    public $resetPassFormProcessor;

    /**
     * @inject
     * @var \occ2\inventar\User\controls\forms\ControlQuestionFormProcessor
     */
    public $controlQuestionFormProcessor;

    /**
     * @inject
     * @var \occ2\inventar\User\controls\forms\ControlRecaptchaFormProcessor
     */
    public $controlRecaptchaFormProcessor;

    /**
     * @inject
     * @var \occ2\inventar\User\controls\forms\ExpiredPassFormProcessor
     */
    public $expiredPassFormProcessor;

    /**
     * @inject
     * @var \occ2\inventar\User\controls\grids\UserHistoryGridProcessor
     */
    public $userHistoryGridProcessor;
    
    /**
     * @inject
     * @var \occ2\inventar\User\controls\forms\IResetPassForm
     */
    public $resetPassFormFactory;
    
    /**
     * @inject
     * @var \occ2\inventar\User\controls\forms\IControlQuestionForm
     */
    public $controlQuestionFormFactory;
    
    /**
     * @inject
     * @var \occ2\inventar\User\controls\forms\IControlRecaptchaForm
     */
    public $controlRecapchaFormFactory;

    /**
     * @inject
     * @var \occ2\inventar\User\controls\forms\IExpiredPassForm
     */
    public $expiredPassFormFactory;
       
    /**
     * @inject
     * @var \occ2\inventar\User\controls\grids\IUserHistoryGrid
     */
    public $userHistoryGridFactory;
    
    /**
     * @inject
     * @var \occ2\inventar\User\models\facades\Profiles
     */
    public $usersFacade;
    
    /**
     * @var \Nette\Http\SessionSection
     */
    public $resetSession;
    
    /**
     * @var string | null
     */
    public $username=null;
    
    /**
     * @var string | null
     */
    public $email=null;
    
    /**
     * @var string | null
     */
    public $question=null;
    
    /**
     * @var string | null
     */
    public $answer=null;
    
    /**
     * @var int | null
     */
    public $stage=null;
    
    /**
     * startup (load properties from session etc.)
     * @return void
     */
    public function startup()
    {
        $this->resetSession = $this->getSession(self::RESET_PASS_FORM);
        $this->username = $this->resetSession->{ResetPassForm::USERNAME};
        $this->email = $this->resetSession->{ResetPassForm::EMAIL};
        $this->question = $this->resetSession->question;
        $this->answer = $this->resetSession->{ControlQuestionForm::ANSWER};
        $this->stage = $this->resetSession->stage;
        parent::startup();
        if ($this->user->isLoggedIn()) {
            if ($this->user->identity->currentAccount) {
                $this->user->getAuthorizator()->setAccount($this->user->identity->currentAccount);
            }
        }
        return;
    }

    /**
     * @return \occ2\inventar\User\controls\forms\SignInForm
     */
    public function createComponentSignInForm()
    {
        $form = $this->signInFormFactory->create();
        $this->signInFormProcessor->process($form,$this);
        return $form;
    }
    
    /**
     * @return \occ2\inventar\User\controls\forms\RegisterForm
     */
    public function createComponentRegisterForm()
    {
        $form = $this->registerFormFactory->create();
        $this->registerFormProcessor->process($form, $this);
        return $form;
    }
    
    /**
     * @return \occ2\inventar\User\controls\forms\ResetPassForm
     */
    public function createComponentResetPassForm()
    {
        $form = $this->resetPassFormFactory->create();
        $this->resetPassFormProcessor->process($form, $this);
        return $form;
    }
    
    /**
     * @return \occ2\inventar\User\controls\forms\ControlQuestionForm
     */
    public function createComponentControlQuestionForm()
    {
        $form = $this->controlQuestionFormFactory->create();
        $this->controlQuestionFormProcessor->process($form, $this);
        return $form;
    }
    
    /**
     * @return \occ2\inventar\User\controls\forms\ControlRecaptchaForm
     */
    public function createComponentControlRecaptchaForm()
    {
        $form = $this->controlRecapchaFormFactory->create();
        $this->controlRecaptchaFormProcessor->process($form, $this);
        return $form;
    }
    
    /**
     * @return \occ2\inventar\User\controls\forms\ExpiredPassForm
     */
    public function createComponentExpiredPassForm()
    {
        $form = $this->expiredPassFormFactory->create();
        $this->expiredPassFormProcessor->process($form, $this);
        return $form;
    }

    /**
     * @return \occ2\inventar\User\controls\grids\UserHistoryGrid
     */
    public function createComponentUserHistoryGrid()
    {
        $grid = $this->userHistoryGridFactory->create();
        $this->userHistoryGridProcessor->process($grid, $this);
        return $grid;
    }
    
    /**
     * default action
     * @return void
     * @title user.signInForm.title
     */
    public function actionDefault()
    {
        if (!$this->user->isLoggedIn()) {
            $this->redirect(self::ACTION_SIGNIN);
        }
        $this[self::USER_HISTORY_GRID]->setDatasource($this->usersFacade->getHistory($this->user->getId()));
        return;
    }
    
    /**
     * sign in action
     * @return void
     * @title user.signInForm.title
     * @acl (loggedIn=false)
     */
    public function actionSignIn()
    {
        $this[self::BREADCRUMBS]->removeItem("home");
        if ($this->user->isLoggedIn()) {
            $this->redirect(self::ACTION_DEFAULT);
        }
        return;
    }
    
    /**
     * signout action
     * @return void
     * @acl (loggedIn=true)
     */
    public function actionSignOut()
    {
        if ($this->user->isLoggedIn()) {
            $this->user->logout(true);
        }
        $this->flashMessage("user.success.logout", self::STATUS_SUCCESS, "", static::$iconPrefix . static::ICON_SUCCESS);
        $this->redirect(self::ACTION_SIGNIN);
        return;
    }
    
    /**
     * reset action
     * @return void
     * @title user.resetPassForm.title
     * @acl (loggedIn=false)
     */
    public function actionReset()
    {
        $this[self::BREADCRUMBS]->removeItem("home");
        if ($this->user->isLoggedIn()) {
            $this->user->logout(true);
        }
        return;
    }
    
    /**
     * change expired pass action
     * @return void
     * @title user.expiredPassForm.title
     * @acl (loggedIn=false)
     */
    public function actionExpired()
    {
        $this[self::BREADCRUMBS]->removeItem("home");
        if ($this->user->isLoggedIn()) {
            $this->user->logout(true);
        }
        return;
    }
    
    /**
     * set stage to template
     * @return void
     */
    public function renderReset()
    {
        $this->template->stage = $this->stage;
        return;
    }
    
    /**
     * clear reset password
     * @return void
     * @acl (loggedIn=false)
     */
    public function handleClearReset()
    {
        $this->username = $this->resetSession->{ResetPassForm::USERNAME} = null;
        $this->email = $this->resetSession->{ResetPassForm::EMAIL} = null;
        $this->answer = $this->resetSession->{ControlQuestionForm::ANSWER} = null;
        $this->stage = $this->resetSession->stage = null;
        $this->question = $this->resetSession->question = null;
        if ($this->isAjax()) {
            $this->redrawControl();
        } else {
            $this->redirect(self::THIS);
        }
        return;
    }
    
    /**
     * back to reset control question
     * @return void
     * @acl (loggedIn=false)
     */
    public function handleBackToQuestion()
    {
        $this->answer = $this->resetSession->{ControlQuestionForm::ANSWER} = null;
        $this->stage = $this->resetSession->stage = 1;
        if ($this->isAjax()) {
            $this->redrawControl();
        } else {
            $this->redirect(self::THIS);
        }
        return;
    }
}
