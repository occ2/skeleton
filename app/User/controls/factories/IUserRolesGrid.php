<?php
namespace occ2\inventar\User\controls\grids;

/**
 * UsersRolesGrid factory inteface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
interface IUserRolesGrid
{
    /**
     * @return \occ2\inventar\User\controls\grids\UserRolesGrid
     */
    public function create();
}
