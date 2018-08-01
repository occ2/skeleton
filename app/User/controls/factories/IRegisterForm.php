<?php
namespace occ2\inventar\User\controls\forms;

/**
 * IRegisterForm factory interface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
interface IRegisterForm
{
    /**
     * @return \occ2\inventar\User\controls\forms\RegisterForm
     */
    public function create();
}
