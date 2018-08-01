<?php
namespace occ2\inventar\User\controls\grids;

/**
 * UsersConfigGrid factory inteface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
interface IUserConfigGrid
{
    /**
     * @return \occ2\inventar\User\controls\grids\UserConfigGrid
     */
    public function create();
}
