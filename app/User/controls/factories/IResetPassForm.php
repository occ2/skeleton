<?php
namespace occ2\inventar\User\controls\forms;

/**
 * IResetPassForm factory inteface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
interface IResetPassForm
{
    /**
     * @return \occ2\inventar\User\controls\forms\ResetPassForm
     */
    public function create();
}
