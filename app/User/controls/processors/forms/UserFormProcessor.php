<?php
namespace occ2\inventar\User\controls\forms;

use occ2\FormControl\IProcessor;
use occ2\FormControl\FormControl;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;
use occ2\inventar\User\models\repositories\Users;
use occ2\inventar\User\models\exceptions\UsersException;
use occ2\inventar\User\presenters\ManagerPresenter;

/**
 * UserFormProcessor
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class UserFormProcessor implements IProcessor
{
    /**
     * @param FormControl $form
     * @param Presenter $presenter
     */
    public function process(FormControl $form, Presenter $presenter){
        $form->onSuccess[]=function (Form $form) use ($presenter) {
            try {
                $values = $form->getValues();
                if(!isset($values->{Users::ID}) || empty($values->{Users::ID})){
                    $presenter->usersFacade->addUser($form->getValues());
                    $presenter[ManagerPresenter::USERS_GRID]->flashMessage($presenter->text("user.success.addUser"), 'success');
                } else {
                    $presenter->usersFacade->editUser($form->getValues());
                    $presenter[ManagerPresenter::USERS_GRID]->flashMessage($presenter->text("user.success.editUser"), 'success');
                }
                $presenter->redirect("default");
                return;
            } catch (UsersException $exc) {
                bdump($exc);
                if($exc->getCode()== UsersException::USERNAME_NOT_UNIQUE) {
                    $presenter[ManagerPresenter::USER_FORM]->throwError(UserForm::USERNAME,"user.error.uniqueUsername");
                    $presenter[ManagerPresenter::USER_FORM]->reload();
                }
            }
            return;
        };
    }
}