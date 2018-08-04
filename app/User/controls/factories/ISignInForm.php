<?php
namespace app\User\controls\factories;

/**
 * ISignInForm factory inteface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
interface ISignInForm
{
    /**
     * @return \app\User\controls\forms\SignInForm
     */
    public function create();
}
