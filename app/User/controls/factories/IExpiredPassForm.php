<?php
namespace occ2\inventar\User\controls\forms;

/**
 * IExpiredPassForm factory interface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
interface IExpiredPassForm
{
    /**
     * @return \occ2\inventar\User\controls\forms\ExpiredPassForm
     */
    public function create();
}
