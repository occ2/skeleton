<?php
namespace occ2\inventar\User\controls\grids;

/**
 * IUserHistoryGrid factory inteface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
interface IUserHistoryGrid
{
    /**
     * @return \occ2\inventar\User\controls\grids\UserHistoryGrid
     */
    public function create();
}
