<?php
namespace occ2\inventar\User\controls\grids;

/**
 * ISignInForm factory inteface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
interface IUsersGrid
{
    /**
     * @return \occ2\inventar\User\controls\grids\UsersGrid
     */
    public function create();
}
