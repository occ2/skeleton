<?php
namespace occ2\inventar\User\controls\forms;

/**
 * IUserAddForm factory interface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
interface IUserForm
{
    /**
     * @return \occ2\inventar\User\controls\forms\UserForm
     */
    public function create();
}
