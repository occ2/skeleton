<?php
namespace occ2\inventar\User\controls\forms;

/**
 * ISignInForm factory inteface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
interface ISignInForm
{
    /**
     * @return \occ2\inventar\User\controls\forms\SignInForm
     */
    public function create();
}
